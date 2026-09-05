import { put } from '@vercel/blob'
import crypto from 'node:crypto'
import { NextRequest, NextResponse } from 'next/server'

export async function POST(request: NextRequest) {
  const expected = process.env.ADMIN_SESSION_TOKEN || crypto.createHash('sha256').update(process.env.ADMIN_PASSWORD || process.env.BETTER_AUTH_SECRET || '').digest('hex')
  if (request.cookies.get('dwipantara_admin')?.value !== expected) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const formData = await request.formData()
  const file = formData.get('file')
  if (!(file instanceof File)) return NextResponse.json({ error: 'File gambar wajib dipilih.' }, { status: 400 })
  if (!file.type.startsWith('image/') || file.size > 5 * 1024 * 1024) return NextResponse.json({ error: 'Gunakan gambar maksimal 5MB.' }, { status: 400 })
  const blob = await put(`dwipantara/${Date.now()}-${file.name.replace(/[^a-zA-Z0-9._-]/g, '-')}`, file, { access: 'public' })
  return NextResponse.json({ url: blob.url })
}
