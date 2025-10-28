// Ticket LocalStorage Management System
// All tickets are stored per user in localStorage

const STORAGE_KEYS = {
    TICKETS: 'ticketapp_tickets',
    SEARCH_QUERY: 'fixflow_search_query',
    DRAFT_TICKET: 'fixflow_draft_ticket'
};

// Get current user from session
function getCurrentUser() {
    const session = localStorage.getItem('ticketapp_session');
    if (!session) return null;

    try {
        const sessionData = JSON.parse(session);
        return sessionData.user;
    } catch (e) {
        return null;
    }
}

// Get user-specific ticket storage key
function getUserTicketsKey() {
    const user = getCurrentUser();
    if (!user) return null;
    return `${STORAGE_KEYS.TICKETS}_${user.id}`;
}

// Get all tickets for current user
function getUserTickets() {
    const key = getUserTicketsKey();
    if (!key) return [];

    const ticketsJson = localStorage.getItem(key);
    if (!ticketsJson) return [];

    try {
        return JSON.parse(ticketsJson);
    } catch (e) {
        console.error('Error parsing tickets:', e);
        return [];
    }
}

// Save tickets for current user
function saveUserTickets(tickets) {
    const key = getUserTicketsKey();
    if (!key) {
        console.error('No user session found');
        return false;
    }

    try {
        localStorage.setItem(key, JSON.stringify(tickets));
        return true;
    } catch (e) {
        console.error('Error saving tickets:', e);
        return false;
    }
}

// Create a new ticket
function createTicket(ticketData) {
    const user = getCurrentUser();
    if (!user) return null;

    const tickets = getUserTickets();

    const newTicket = {
        id: 'ticket_' + Date.now() + Math.random().toString(36).substr(2, 9),
        title: ticketData.title,
        description: ticketData.description || '',
        status: ticketData.status,
        priority: ticketData.priority || 'medium',
        userId: user.id,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
    };

    tickets.push(newTicket);
    saveUserTickets(tickets);

    return newTicket;
}

// Update an existing ticket
function updateTicket(ticketId, ticketData) {
    const tickets = getUserTickets();
    const index = tickets.findIndex(t => t.id === ticketId);

    if (index === -1) return null;

    tickets[index] = {
        ...tickets[index],
        title: ticketData.title,
        description: ticketData.description || '',
        status: ticketData.status,
        priority: ticketData.priority || 'medium',
        updatedAt: new Date().toISOString()
    };

    saveUserTickets(tickets);
    return tickets[index];
}

// Delete a ticket
function deleteTicket(ticketId) {
    const tickets = getUserTickets();
    const filteredTickets = tickets.filter(t => t.id !== ticketId);

    if (filteredTickets.length === tickets.length) {
        return false; // Ticket not found
    }

    saveUserTickets(filteredTickets);
    return true;
}

// Get a single ticket by ID
function getTicketById(ticketId) {
    const tickets = getUserTickets();
    return tickets.find(t => t.id === ticketId) || null;
}

// Get ticket statistics
function getTicketStats() {
    const tickets = getUserTickets();

    const stats = {
        total: tickets.length,
        open: 0,
        in_progress: 0,
        closed: 0
    };

    tickets.forEach(ticket => {
        if (stats.hasOwnProperty(ticket.status)) {
            stats[ticket.status]++;
        }
    });

    return stats;
}

// Search tickets
function searchTickets(query) {
    const tickets = getUserTickets();

    if (!query) return tickets;

    const lowerQuery = query.toLowerCase();

    return tickets.filter(ticket => {
        return ticket.title.toLowerCase().includes(lowerQuery) ||
               (ticket.description && ticket.description.toLowerCase().includes(lowerQuery)) ||
               ticket.status.toLowerCase().includes(lowerQuery);
    });
}
