import { NextRequest, NextResponse } from 'next/server'
import crypto from 'node:crypto'

export async function POST(request: NextRequest) {
  const { password } = await request.json()
  const expected = process.env.ADMIN_PASSWORD || process.env.BETTER_AUTH_SECRET
  if (!expected || typeof password !== 'string' || password.length === 0) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const valid = password.length === expected.length && crypto.timingSafeEqual(Buffer.from(password), Buffer.from(expected))
  if (!valid) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const response = NextResponse.json({ success: true })
  response.cookies.set('dwipantara_admin', process.env.ADMIN_SESSION_TOKEN || crypto.createHash('sha256').update(expected).digest('hex'), { httpOnly: true, secure: process.env.NODE_ENV === 'production', sameSite: 'lax', maxAge: 60 * 60 * 8, path: '/' })
  return response
}
