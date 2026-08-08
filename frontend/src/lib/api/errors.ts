import axios from "axios"

// Matches API_CONTRACTS.md §1's error envelope:
// { message: string, errors?: Record<string, string[]> }
type ApiErrorBody = {
  message?: string
  errors?: Record<string, string[]>
}

export function getValidationErrors(
  error: unknown
): Record<string, string[]> | null {
  if (
    axios.isAxiosError<ApiErrorBody>(error) &&
    error.response?.status === 422
  ) {
    return error.response.data.errors ?? null
  }

  return null
}

export function getErrorMessage(error: unknown, fallback: string): string {
  if (axios.isAxiosError<ApiErrorBody>(error)) {
    return error.response?.data.message ?? fallback
  }

  return fallback
}
