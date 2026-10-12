const API_BASE = '/api/tickets'

async function request(url, options = {}) {
  const response = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  const data = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(data.error || `Request failed: ${response.status}`)
  }

  return data
}

export function getTickets() {
  return request(API_BASE)
}

export function getTicket(id) {
  return request(`${API_BASE}/${id}`)
}

export function createTicket(ticket) {
  return request(API_BASE, {
    method: 'POST',
    body: JSON.stringify(ticket),
  })
}

export function updateTicket(id, changes) {
  return request(`${API_BASE}/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(changes),
  })
}

export function getTicketStats() {
  return request(`${API_BASE}/stats`)
}