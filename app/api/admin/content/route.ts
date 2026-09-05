import { NextRequest, NextResponse } from 'next/server'
import crypto from 'node:crypto'
import { query } from '@/lib/db'

async function requireAdmin(request: NextRequest) {
  const token = request.cookies.get('dwipantara_admin')?.value
  const expected = process.env.ADMIN_SESSION_TOKEN || crypto.createHash('sha256').update(process.env.ADMIN_PASSWORD || process.env.BETTER_AUTH_SECRET || '').digest('hex')
  return Boolean(token) && token === expected
}

export async function GET(request: NextRequest) {
  if (!(await requireAdmin(request))) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const result = await query('SELECT id, title, slug, description, "imagePath", published, "createdAt", "updatedAt" FROM event_content ORDER BY "createdAt" DESC')
  return NextResponse.json({ items: result.rows })
}

export async function POST(request: NextRequest) {
  if (!(await requireAdmin(request))) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const body = await request.json()
  const title = String(body.title || '').trim()
  const description = String(body.description || '').trim()
  const slug = String(body.slug || title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')).trim()
  if (!title || !description || !slug) return NextResponse.json({ error: 'Judul, slug, dan deskripsi wajib diisi.' }, { status: 400 })
  const result = await query('INSERT INTO event_content (title, slug, description, "imagePath", published) VALUES ($1, $2, $3, $4, $5) RETURNING *', [title, slug, description, body.imagePath || null, body.published !== false])
  return NextResponse.json({ item: result.rows[0] }, { status: 201 })
}

export async function PUT(request: NextRequest) {
  if (!(await requireAdmin(request))) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const body = await request.json()
  const id = Number(body.id)
  if (!id) return NextResponse.json({ error: 'ID konten tidak valid.' }, { status: 400 })
  const result = await query('UPDATE event_content SET title = $1, slug = $2, description = $3, "imagePath" = $4, published = $5, "updatedAt" = now() WHERE id = $6 RETURNING *', [body.title, body.slug, body.description, body.imagePath || null, body.published !== false, id])
  if (!result.rows[0]) return NextResponse.json({ error: 'Konten tidak ditemukan.' }, { status: 404 })
  return NextResponse.json({ item: result.rows[0] })
}

export async function DELETE(request: NextRequest) {
  if (!(await requireAdmin(request))) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const id = Number(new URL(request.url).searchParams.get('id'))
  await query('DELETE FROM event_content WHERE id = $1', [id])
  return NextResponse.json({ success: true })
}
