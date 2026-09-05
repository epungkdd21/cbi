import { NextResponse } from 'next/server'

const paymentEndpoint = process.env.PAYKITA_API_URL || 'https://pay.digikita.id/api/orders'

function readable(value: unknown): string | undefined {
  if (typeof value === 'string') return value
  if (value && typeof value === 'object') {
    const record = value as Record<string, unknown>
    for (const key of ['message', 'error', 'detail', 'description']) {
      const nested = readable(record[key])
      if (nested) return nested
    }
    return JSON.stringify(value)
  }
  return value == null ? undefined : String(value)
}

function findValue(value: unknown, keys: string[]): string | undefined {
  if (!value || typeof value !== 'object') return undefined
  const record = value as Record<string, unknown>
  for (const key of keys) {
    const candidate = record[key]
    if (typeof candidate === 'string' && candidate.trim()) return candidate
  }
  for (const nested of Object.values(record)) {
    const found = findValue(nested, keys)
    if (found) return found
  }
  return undefined
}

export async function POST(request: Request) {
  const apiKey = process.env.PAYKITA_API_KEY
  if (!apiKey) return NextResponse.json({ error: 'PayKita belum dikonfigurasi di server.' }, { status: 503 })

  try {
    const body = await request.json()
    const reference = `DWP-${Date.now()}`
    const response = await fetch(paymentEndpoint, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'x-api-key': apiKey },
      body: JSON.stringify({ base_amount: body.amount, reference }),
      cache: 'no-store',
    })
    const result = await response.json().catch(() => null)
    if (!response.ok || result?.ok === false) {
      const providerMessage = readable(result?.error) || readable(result?.message)
      return NextResponse.json({ error: providerMessage ? `PayKita: ${providerMessage}` : `PayKita menolak transaksi (HTTP ${response.status}).` }, { status: response.status || 502 })
    }

    const data = result?.data || result?.payment || result
    const qrImage = findValue(result, ['qr_image', 'qrImage', 'qr_code_image', 'qrCodeImage', 'qr_url', 'qrUrl', 'image_url', 'imageUrl'])
    const qris = findValue(result, ['qris', 'qr_string', 'qrString', 'qr_content', 'qrContent', 'qr_code', 'qrCode'])
    return NextResponse.json({
      qris,
      qrImage,
      paymentUrl: findValue(result, ['payment_url', 'paymentUrl', 'redirect_url', 'redirectUrl']),
      transactionId: findValue(result, ['transaction_id', 'transactionId', 'reference', 'id']),
      expiresAt: findValue(result, ['expires_at', 'expiresAt']),
      providerResponse: data,
    })
  } catch {
    return NextResponse.json({ error: 'Tidak dapat terhubung ke PayKita. Coba lagi.' }, { status: 502 })
  }
}
