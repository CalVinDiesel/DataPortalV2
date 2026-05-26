'use strict';
/**
 * 🚀 NITRO ORCHESTRATOR WORKER (v310)
 * ─────────────────────────────────────────────────────────────────────────────
 * Runs the ENTIRE upload pipeline (batching, concurrency, retries, XHR)
 * inside this dedicated worker thread.
 *
 * WHY A SEPARATE FILE (not inline Blob URL):
 *   Blob-URL workers can be blocked by browser internals and have no error
 *   visibility. A real static worker file is fully debuggable, CSP-compatible,
 *   and guaranteed to start reliably.
 *
 * WHY A WORKER AT ALL:
 *   Browser background-tab throttling only affects the MAIN thread. Worker
 *   threads run at full speed regardless of tab visibility. By moving ALL
 *   upload sequencing here, Alt-Tab never pauses the upload.
 * ─────────────────────────────────────────────────────────────────────────────
 */

const MAX_CHUNK_SIZE  = 2 * 1024 * 1024; // 2 MB per batch
const MAX_BATCH_COUNT = 30;               // max files per batch
const LANE_COUNT      = 4;               // concurrent upload lanes

let cfg         = null;
let isPaused    = false;
let pauseQueue  = [];
let overallSent = 0;

// ── Message Router ────────────────────────────────────────────────────────────
self.onmessage = async (e) => {
    const msg = e.data;

    if (msg.type === 'start') {
        cfg         = msg;
        overallSent = 0;

        try {
            // Web Locks prevent the browser/OS from hibernating this worker
            if ('locks' in navigator) {
                await navigator.locks.request('nitro-upload-v310', async () => {
                    await runPipeline(msg.files, msg.paths);
                });
            } else {
                await runPipeline(msg.files, msg.paths);
            }
            self.postMessage({ type: 'complete' });

        } catch (err) {
            self.postMessage({ type: 'error', msg: String(err.message || err) });
        }

    } else if (msg.type === 'pause') {
        isPaused = true;

    } else if (msg.type === 'resume') {
        isPaused = false;
        const queue = pauseQueue.splice(0);
        queue.forEach(r => r());
    }
};

// Catch any unhandled errors/rejections in the worker
self.addEventListener('error', (e) => {
    self.postMessage({ type: 'error', msg: 'Worker error: ' + (e.message || e) });
});
self.addEventListener('unhandledrejection', (e) => {
    self.postMessage({ type: 'error', msg: 'Unhandled rejection: ' + (e.reason?.message || e.reason || 'unknown') });
});

// ── Pause Helper ─────────────────────────────────────────────────────────────
async function waitIfPaused() {
    if (isPaused) await new Promise(r => pauseQueue.push(r));
}

// ── Pipeline: Build Batches → Run Lanes ──────────────────────────────────────
async function runPipeline(files, paths) {
    // Sanity check
    if (!files || files.length === 0) {
        throw new Error('No files received by worker.');
    }

    // Build all batches upfront
    const batches = [];
    let fileIdx = 0;
    while (fileIdx < files.length) {
        let bFiles = [], bPaths = [], bSize = 0;
        while (fileIdx < files.length) {
            const f = files[fileIdx];
            if (f.size > MAX_CHUNK_SIZE) {
                // Large file gets its own batch
                if (bFiles.length > 0) break;
                bFiles.push(f);
                bPaths.push(paths[fileIdx]);
                fileIdx++;
                break;
            }
            if (bSize + f.size > MAX_CHUNK_SIZE || bFiles.length >= MAX_BATCH_COUNT) break;
            bFiles.push(f);
            bPaths.push(paths[fileIdx]);
            bSize  += f.size;
            fileIdx++;
        }
        if (bFiles.length > 0) batches.push({ files: bFiles, paths: bPaths });
    }

    self.postMessage({ type: 'debug', msg: `Worker: ${files.length} files → ${batches.length} batches` });

    if (batches.length === 0) return; // nothing to do

    // Concurrent lanes — all running inside this worker, never touching main thread
    let nextIdx = 0;

    async function lane(laneId) {
        while (nextIdx < batches.length) {
            await waitIfPaused();
            const idx = nextIdx++;
            if (idx >= batches.length) break;

            const b    = batches[idx];
            const port = cfg.isDev ? (9001 + (idx % 16)) : 9001;

            let retries = 0;
            while (retries < 3) {
                try {
                    const sent = await sendBatch(b.files, b.paths, port, idx);
                    overallSent += sent;
                    self.postMessage({ type: 'progress', sent: overallSent, total: cfg.totalSize });
                    break;
                } catch (err) {
                    retries++;
                    if (retries >= 3) throw new Error(`Batch ${idx} failed after 3 retries: ${err.message}`);
                    await new Promise(r => setTimeout(r, 1500 * retries));
                }
            }
        }
    }

    const activeLanes = Math.min(LANE_COUNT, batches.length);
    await Promise.all(Array.from({ length: activeLanes }, (_, i) => lane(i)));
}

// ── XHR Uploader ─────────────────────────────────────────────────────────────
function sendBatch(files, paths, port, idx) {
    return new Promise((resolve, reject) => {
        // Build Nitro multiplex binary protocol:
        // [4 bytes path length][N bytes path][8 bytes file size][M bytes file data] × N files
        const enc   = new TextEncoder();
        const parts = [];
        for (let i = 0; i < files.length; i++) {
            const pb  = enc.encode(paths[i]);
            const hdr = new ArrayBuffer(4 + pb.length + 8);
            const dv  = new DataView(hdr);
            dv.setUint32(0, pb.length, true);
            new Uint8Array(hdr, 4, pb.length).set(pb);
            dv.setFloat64(4 + pb.length, files[i].size, true);
            parts.push(hdr);
            parts.push(files[i]); // File is a Blob — works natively in workers
        }
        const blob = new Blob(parts, { type: 'application/octet-stream' });

        // Build upload URL
        const qs = 'projectID=' + encodeURIComponent(cfg.projectID)
                 + '&isFirstChunk=true&slot=w' + idx;
        const url = cfg.isDev
            ? ('http://' + cfg.host + ':' + port + '/nitro_upload.php?' + qs)
            : (cfg.directRoute + '?' + qs);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', cfg.csrf);
        xhr.setRequestHeader('Content-Type', 'application/octet-stream');

        xhr.upload.onprogress = ev => {
            if (ev.lengthComputable) {
                self.postMessage({ type: 'lane_progress', loaded: ev.loaded, total: ev.total });
            }
        };

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(blob.size);
            } else {
                reject(new Error('HTTP ' + xhr.status + ' from port ' + port));
            }
        };
        xhr.onerror   = () => reject(new Error('Network error on port ' + port));
        xhr.ontimeout = () => reject(new Error('Timeout on port ' + port));
        xhr.timeout   = 300000; // 5 minutes max per batch

        xhr.send(blob);
    });
}
