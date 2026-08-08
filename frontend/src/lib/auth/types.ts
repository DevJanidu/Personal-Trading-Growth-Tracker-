// Mirrors App\Http\Resources\UserResource (backend/app/Http/Resources/UserResource.php).
export type AuthUser = {
  id: number
  name: string
  email: string
  timezone: string
  default_currency: string
  theme: "light" | "dark" | "system" | null
  created_at: string
}
