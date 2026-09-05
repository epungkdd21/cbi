'use client'

import { FormEvent, useState } from 'react'
import { useRouter } from 'next/navigation'

export default function AdminLoginPage() {
  const router = useRouter()
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  async function submit(event: FormEvent) {
    event.preventDefault()
    const response = await fetch('/api/admin/login', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ password }) })
    if (!response.ok) { setError('Password admin tidak sesuai.'); return }
    router.push('/admin')
    router.refresh()
  }
  return <main className="admin-shell"><section className="admin-login-card"><p className="admin-kicker">DWIPANTARA / ADMIN</p><h1>Kelola cerita<br /><em>di balik layar.</em></h1><p className="admin-muted">Masuk untuk mengatur kegiatan, gambar, dan konten beranda Dwipantara.</p><form onSubmit={submit}><label>Password admin<input required type="password" value={password} onChange={(event) => setPassword(event.target.value)} placeholder="Masukkan password" /></label>{error && <p className="admin-error">{error}</p>}<button className="admin-button">Masuk ke dashboard <span>→</span></button></form></section></main>
}
