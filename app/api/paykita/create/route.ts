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
    return NextResponse.json({
      qris: data?.qris,
      qrImage: data?.qr_code || data?.qrCode || data?.qr_image || data?.qrImage || data?.qr_url || data?.qrUrl,
      paymentUrl: data?.payment_url || data?.paymentUrl || data?.redirect_url || data?.redirectUrl,
      transactionId: data?.transaction_id || data?.transactionId || data?.id,
      expiresAt: data?.expires_at,
    })
  } catch {
    return NextResponse.json({ error: 'Tidak dapat terhubung ke PayKita. Coba lagi.' }, { status: 502 })
  }
}
