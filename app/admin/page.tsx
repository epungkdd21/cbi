'use client'

import { FormEvent, useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'

type Item = { id: number; title: string; slug: string; description: string; imagePath?: string | null; published: boolean }
const empty = { title: '', slug: '', description: '', imagePath: '', published: true }

export default function AdminPage() {
  const router = useRouter()
  const [items, setItems] = useState<Item[]>([])
  const [form, setForm] = useState(empty)
  const [editing, setEditing] = useState<number | null>(null)
  const [notice, setNotice] = useState('')
  const [uploading, setUploading] = useState(false)

  async function load() {
    const response = await fetch('/api/admin/content')
    if (response.status === 401) { router.replace('/admin/login'); return }
    const data = await response.json()
    setItems(data.items || [])
  }
  useEffect(() => { load() }, [])

  async function upload(file?: File) {
    if (!file) return
    setUploading(true)
    const data = new FormData(); data.append('file', file)
    const response = await fetch('/api/admin/upload', { method: 'POST', body: data })
    const result = await response.json()
    setUploading(false)
    if (!response.ok) { setNotice(result.error || 'Upload gagal.'); return }
    setForm((current) => ({ ...current, imagePath: result.url })); setNotice('Gambar berhasil diunggah.')
  }
  async function save(event: FormEvent) {
    event.preventDefault(); setNotice('Menyimpan...')
    const response = await fetch('/api/admin/content', { method: editing ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(editing ? { ...form, id: editing } : form) })
    const result = await response.json()
    if (!response.ok) { setNotice(result.error || 'Gagal menyimpan.'); return }
    setForm(empty); setEditing(null); setNotice('Konten berhasil disimpan.'); load()
  }
  async function remove(id: number) {
    if (!confirm('Hapus konten ini?')) return
    await fetch(`/api/admin/content?id=${id}`, { method: 'DELETE' }); load()
  }
  return <main className="admin-shell"><header className="admin-header"><div><p className="admin-kicker">DWIPANTARA / CONTROL ROOM</p><h1>Dashboard <em>event.</em></h1><p className="admin-muted">Kelola cerita dan gambar yang tampil di beranda.</p></div><button className="admin-quiet" onClick={() => router.push('/')}>Lihat website ↗</button></header><section className="admin-grid"><form className="admin-editor" onSubmit={save}><div className="admin-section-head"><div><p className="admin-kicker">{editing ? 'EDIT KONTEN' : 'KONTEN BARU'}</p><h2>{editing ? 'Perbarui kegiatan' : 'Tambah kegiatan'}</h2></div>{editing && <button type="button" className="admin-quiet" onClick={() => { setEditing(null); setForm(empty) }}>Batal</button>}</div><label>Judul kegiatan<input required value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} placeholder="Pemutaran film Dwipantara" /></label><label>Slug<input required value={form.slug} onChange={(e) => setForm({ ...form, slug: e.target.value })} placeholder="pemutaran-film-dwipantara" /></label><label>Deskripsi<textarea required rows={6} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} placeholder="Ceritakan kegiatan Dwipantara..." /></label><label>Gambar kegiatan<input type="file" accept="image/*" onChange={(e) => upload(e.target.files?.[0])} />{form.imagePath && <img className="admin-preview" src={form.imagePath} alt="Pratinjau kegiatan" />}</label><label className="admin-check"><input type="checkbox" checked={form.published} onChange={(e) => setForm({ ...form, published: e.target.checked })} /> Tampilkan di beranda</label>{notice && <p className="admin-notice">{uploading ? 'Mengunggah gambar...' : notice}</p>}<button className="admin-button" disabled={uploading}>{editing ? 'Simpan perubahan' : 'Publikasikan kegiatan'} <span>→</span></button></form><section className="admin-list"><div className="admin-section-head"><div><p className="admin-kicker">BERANDA / KONTEN</p><h2>Kegiatan terbit</h2></div><span className="admin-count">{items.length} item</span></div>{items.length === 0 ? <div className="admin-empty">Belum ada kegiatan. Tambahkan cerita pertama Dwipantara.</div> : <div className="admin-items">{items.map((item) => <article className="admin-item" key={item.id}>{item.imagePath ? <img src={item.imagePath} alt="" /> : <div className="admin-image-empty">D</div>}<div className="admin-item-body"><div><span className="admin-status">{item.published ? 'TERBIT' : 'DRAFT'}</span><h3>{item.title}</h3><p>{item.description}</p></div><div className="admin-actions"><button onClick={() => { setEditing(item.id); setForm({ title: item.title, slug: item.slug, description: item.description, imagePath: item.imagePath || '', published: item.published }) }}>Edit</button><button onClick={() => remove(item.id)}>Hapus</button></div></div></article>)}</div>}</section></section></main>
}
