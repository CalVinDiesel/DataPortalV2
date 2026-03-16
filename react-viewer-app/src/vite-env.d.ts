/* Vite client types - avoid requiring "vite/client" in tsconfig to prevent "Cannot find type definition file" when types are not resolved */
interface ImportMeta {
  readonly env: Record<string, string | undefined>;
  readonly hot?: { accept: () => void; dispose: (cb: () => void) => void };
}

/* Allow CSS imports (TS2307 fix) */
declare module '*.css' {
  const content: Record<string, string>;
  export default content;
}
declare module 'cesium/Build/Cesium/Widgets/widgets.css';
