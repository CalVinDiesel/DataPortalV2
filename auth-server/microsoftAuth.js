// auth-server/microsoftAuth.js
// Microsoft Authentication using @azure/msal-node
// Compatible with ESM ("type": "module"), Express v4, express-session

import * as msal from "@azure/msal-node";

// ─── MSAL Configuration ───────────────────────────────────────────────────────

const msalConfig = {
    auth: {
      clientId: process.env.MICROSOFT_CLIENT_ID,
      authority: `https://login.microsoftonline.com/common`,
      clientSecret: process.env.MICROSOFT_CLIENT_SECRET,
      validateAuthority: false,  // required when using /common with personal accounts
    },

  system: {
    loggerOptions: {
      loggerCallback(loglevel, message) {
        // Uncomment for debugging MSAL internals:
        // console.log("[MSAL]", message);
      },
      piiLoggingEnabled: false,
      logLevel: msal.LogLevel.Warning,
    },
  },
};

// Confidential client = server-side app with a secret (correct for Express backends)
const msalClient = new msal.ConfidentialClientApplication(msalConfig);

// Scopes: openid + profile + email gives you name, email, and unique ID
const SCOPES = ["openid", "profile", "email", "User.Read"];

// ─── Step 1: Generate Login URL ───────────────────────────────────────────────

/**
 * Call this when the user clicks "Sign in with Microsoft".
 * Returns a URL to redirect the user to Microsoft's login page.
 */
// Store PKCE verifier in memory (keyed by state) so callback can use it
const pkceStore = {};

export async function getMicrosoftAuthUrl() {
  const cryptoProvider = new msal.CryptoProvider();
  const { verifier, challenge } = await cryptoProvider.generatePkceCodes();

  const state = Math.random().toString(36).substring(2);
  pkceStore[state] = verifier; // save verifier for callback

  const authUrlParams = {
    scopes: SCOPES,
    redirectUri: process.env.MICROSOFT_REDIRECT_URI,
    codeChallenge: challenge,
    codeChallengeMethod: "S256",
    state,
  };

  const authUrl = await msalClient.getAuthCodeUrl(authUrlParams);
  return { authUrl, state };
}

// ─── Step 2: Exchange Code for Token + User Info ──────────────────────────────

/**
 * After Microsoft redirects back with ?code=..., call this to:
 * 1. Exchange the code for tokens
 * 2. Extract the user's profile from the ID token claims
 *
 * Returns a normalized user object: { id, email, name, provider }
 */
export async function handleMicrosoftCallback(code, state) {
    const verifier = pkceStore[state];
    if (verifier) delete pkceStore[state]; // clean up
  
    const tokenRequest = {
      code,
      scopes: SCOPES,
      redirectUri: process.env.MICROSOFT_REDIRECT_URI,
      codeVerifier: verifier,
    };
  
    const tokenResponse = await msalClient.acquireTokenByCode(tokenRequest);
    const claims = tokenResponse.idTokenClaims;
  
    const user = {
      id: claims.oid,
      email: claims.email || claims.preferred_username || claims.upn || null,
      name: claims.name || claims.given_name || "Microsoft User",
      provider: "microsoft",
    };
  
    return user;
  }