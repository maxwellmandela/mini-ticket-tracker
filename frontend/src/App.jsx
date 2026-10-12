import { useEffect, useState } from 'react'
import './App.css'
import {
  getTickets,
  getTicketStats,
  createTicket,
  updateTicket,
} from './api'

function App() {
  const [tickets, setTickets] = useState([])
  const [stats, setStats] = useState({
    total: 0,
    open: 0,
    inProgress: 0,
    closed: 0,
  })

  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [priority, setPriority] = useState('medium')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  async function loadData() {
    setLoading(true)
    setError('')

    try {
      const [ticketData, statsData] = await Promise.all([
        getTickets(),
        getTicketStats(),
      ])

      // Adjust these two lines if your PHP API uses different response keys.
      setTickets(ticketData.items ?? ticketData.tickets ?? ticketData)
      setStats({
        total: statsData.total ?? 0,
        open: statsData.open ?? 0,
        inProgress: statsData.in_progress ?? statsData.inProgress ?? 0,
        closed: statsData.closed ?? 0,
      })
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    loadData()
  }, [])

  async function handleCreateTicket(event) {
    event.preventDefault()
    setError('')

    try {
      await createTicket({
        title: title.trim(),
        description: description.trim(),
        priority,
      })

      setTitle('')
      setDescription('')
      setPriority('medium')

      await loadData()
    } catch (err) {
      setError(err.message)
    }
  }

  async function handleStatusChange(ticketId, newStatus) {
    setError('')

    try {
      await updateTicket(ticketId, { status: newStatus })
      await loadData()
    } catch (err) {
      setError(err.message)
      await loadData()
    }
  }

  return (
    <main className="dashboard">
      <header className="page-header">
        <div>
          <p className="eyebrow">SERVICE DESK</p>
          <h1>Ticket Tracker</h1>
          <p className="subtitle">
            Manage service requests and track their progress.
          </p>
        </div>
        <span className="header-badge">Mini Tracker</span>
      </header>

      {error && <p role="alert" className="error-message">{error}</p>}

      <section className="stats-grid" aria-label="Ticket statistics">
        <article className="stat-card">
          <p>Total tickets</p>
          <h2>{stats.total}</h2>
        </article>
        <article className="stat-card">
          <p>Open</p>
          <h2>{stats.open}</h2>
        </article>
        <article className="stat-card">
          <p>In progress</p>
          <h2>{stats.inProgress}</h2>
        </article>
        <article className="stat-card">
          <p>Closed</p>
          <h2>{stats.closed}</h2>
        </article>
      </section>

      <section className="content-grid">
        <section className="panel create-panel">
          <div className="panel-heading">
            <h2>Create a ticket</h2>
            <p>Record a new service request.</p>
          </div>

          <form onSubmit={handleCreateTicket}>
            <label htmlFor="title">Title</label>
            <input
              id="title"
              value={title}
              onChange={(event) => setTitle(event.target.value)}
              maxLength={255}
              required
            />

            <label htmlFor="description">Description</label>
            <textarea
              id="description"
              value={description}
              onChange={(event) => setDescription(event.target.value)}
              rows={4}
            />

            <label htmlFor="priority">Priority</label>
            <select
              id="priority"
              value={priority}
              onChange={(event) => setPriority(event.target.value)}
            >
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>

            <button type="submit" className="primary-button">
              + Create ticket
            </button>
          </form>
        </section>

        <section className="panel tickets-panel">
          <div className="panel-heading">
            <div>
              <h2>Tickets</h2>
              <p>Review requests and update their status.</p>
            </div>
            <span className="ticket-count">{tickets.length} tickets</span>
          </div>

          {loading ? (
            <p>Loading tickets...</p>
          ) : tickets.length === 0 ? (
            <p className="empty-state">No tickets found.</p>
          ) : (
            <div className="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Ticket</th>
                    <th>Priority</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {tickets.map((ticket) => (
                    <tr key={ticket.id}>
                      <td>
                        <div className="ticket-title">{ticket.title}</div>
                        <div className="ticket-description">
                          {ticket.description || 'No description provided'}
                        </div>
                        <small>#{ticket.id}</small>
                      </td>
                      <td>
                        <span className={`priority priority-${ticket.priority}`}>
                          {ticket.priority}
                        </span>
                      </td>
                      <td>
                        <select
                          className="status-select"
                          aria-label={`Status for ${ticket.title}`}
                          value={ticket.status}
                          onChange={(event) =>
                            handleStatusChange(ticket.id, event.target.value)
                          }
                        >
                          <option value="open">Open</option>
                          <option value="in_progress">In progress</option>
                          <option value="closed">Closed</option>
                        </select>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </section>
      </section>

      <footer className="page-footer">
        Mini Ticket Tracker · React + PHP API
      </footer>
    </main>
  )
}

export default App