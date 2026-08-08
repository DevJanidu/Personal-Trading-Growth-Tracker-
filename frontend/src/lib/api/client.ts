import axios from "axios"

// Site root (no /api suffix) — needed for Sanctum's /sanctum/csrf-cookie route,
// which lives outside the /api/v1 prefix. See ARCHITECTURE.md §3.
export const API_ROOT_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000"

export const API_BASE_URL = `${API_ROOT_URL}/api/v1`

// Sanctum SPA cookie auth (ARCHITECTURE.md §3): the browser holds an httpOnly
// session cookie set by Laravel, never a bearer token in localStorage.
// `withCredentials` sends it; `withXSRFToken` forces axios to attach the
// X-XSRF-TOKEN header even though the frontend (3000) and API (8000) are on
// different ports/origins in development.
export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: "application/json",
  },
})

/**
 * Bootstraps the CSRF cookie required before any state-changing request.
 * Must be called once (e.g. on app mount) before login, and again after a
 * session expires, per Sanctum's SPA authentication flow.
 */
export async function ensureCsrfCookie(): Promise<void> {
  await axios.get(`${API_ROOT_URL}/sanctum/csrf-cookie`, {
    withCredentials: true,
  })
}

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (
      typeof window !== "undefined" &&
      axios.isAxiosError(error) &&
      error.response?.status === 401 &&
      !window.location.pathname.startsWith("/login")
    ) {
      // Hard navigation (not useRouter) is intentional: a 401 means the
      // session is gone, so we want a full reload that clears all in-memory
      // React/query state, not a client-side transition. This runs in an
      // axios interceptor outside the React tree, where useRouter isn't available.
      // eslint-disable-next-line @next/next/no-location-assign-relative-destination
      window.location.href = "/login"
    }

    return Promise.reject(error)
  }
)
