// Tickets Page - LocalStorage Implementation

// Storage keys for page-specific features
const PAGE_STORAGE_KEYS = {
    SEARCH_QUERY: 'fixflow_search_query',
    DRAFT_TICKET: 'fixflow_draft_ticket'
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAndRenderTickets();
    setupFormHandlers();
    restoreSearchQuery();
    setupAutoSave();
});

// Load and render all tickets
function loadAndRenderTickets() {
    const tickets = getUserTickets();
    renderTickets(tickets);
}

// Render tickets to the grid
function renderTickets(tickets) {
    const grid = document.getElementById('ticketsGrid');
    const emptyState = document.getElementById('emptyState');
    const noResults = document.getElementById('noResults');

    grid.innerHTML = '';

    if (tickets.length === 0) {
        grid.classList.add('hidden');
        emptyState.classList.remove('hidden');
        noResults.classList.add('hidden');
        setTimeout(() => lucide.createIcons(), 100);
        return;
    }

    emptyState.classList.add('hidden');
    grid.classList.remove('hidden');
    noResults.classList.add('hidden');

    tickets.forEach(ticket => {
        const ticketEl = createTicketElement(ticket);
        grid.appendChild(ticketEl);
    });

    // Reinitialize Lucide icons
    setTimeout(() => lucide.createIcons(), 100);
}

// Create ticket element
function createTicketElement(ticket) {
    const div = document.createElement('div');
    div.className = 'bg-white border border-border rounded-xl p-5 shadow-sm hover:shadow-md transition-transform duration-300 ticket-item';
    div.setAttribute('data-title', ticket.title.toLowerCase());
    div.setAttribute('data-description', (ticket.description || '').toLowerCase());
    div.setAttribute('data-status', ticket.status.toLowerCase());

    const statusClasses = {
        'open': 'bg-[#15803D] text-white',
        'in_progress': 'bg-[#FACC15] text-white',
        'closed': 'bg-[#A1A1AA] text-white'
    };

    const priorityClasses = {
        'low': 'bg-muted text-muted-foreground',
        'medium': 'bg-blue-100 text-blue-800',
        'high': 'bg-orange-100 text-orange-800',
        'urgent': 'bg-red-100 text-red-800'
    };

    const statusText = ticket.status.replace('_', ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    const priorityText = ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1);
    const updatedDate = new Date(ticket.updatedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    div.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <h3 class="font-inter text-lg font-semibold flex-1">${escapeHtml(ticket.title)}</h3>
        </div>

        ${ticket.description ? `<p class="text-muted-foreground text-sm mb-3">${escapeHtml(ticket.description)}</p>` : ''}

        <div class="flex flex-wrap gap-2 mb-3">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg ${statusClasses[ticket.status] || 'bg-gray-200 text-gray-800'}">
                ${statusText}
            </span>
            ${ticket.priority ? `<span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg ${priorityClasses[ticket.priority] || 'bg-gray-100 text-gray-800'}">
                ${priorityText}
            </span>` : ''}
        </div>

        <div class="flex justify-between items-center text-xs text-muted-foreground pt-3">
            <p class="flex items-center gap-2">
                <i data-lucide="calendar" class="w-3.5 h-3.5"></i> ${updatedDate}
            </p>
            <div class="flex gap-2 text-sm">
                <button onclick="editTicket('${ticket.id}')" class="inline-flex items-center gap-2 justify-center px-3 py-1.5 text-primary border border-border rounded-lg hover:bg-accent transition-colors">
                    <i data-lucide="edit" class="w-4 h-4 text-primary"></i>Edit
                </button>
                <button onclick="confirmDeleteTicket('${ticket.id}')" class="inline-flex items-center gap-2 justify-center px-3 py-1.5 bg-destructive text-destructive-foreground rounded-lg hover:opacity-90 transition-opacity">
                    <i data-lucide="trash-2" class="w-4 h-4 text-white"></i>Delete
                </button>
            </div>
        </div>
    `;

    return div;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Setup form handlers
function setupFormHandlers() {
    const ticketForm = document.getElementById('ticketForm');
    const deleteForm = document.getElementById('deleteForm');

    // Ticket create/update form
    ticketForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        document.getElementById('titleError').classList.add('hidden');
        document.getElementById('statusError').classList.add('hidden');
        document.getElementById('descriptionError').classList.add('hidden');

        const ticketId = document.getElementById('ticketId').value;
        const title = document.getElementById('title').value.trim();
        const status = document.getElementById('status').value;
        const description = document.getElementById('description').value.trim();
        const priority = document.getElementById('priority').value;

        // Validate
        let hasError = false;

        if (!title) {
            showTicketFieldError('titleError', 'Title is required');
            hasError = true;
        }

        if (!status) {
            showTicketFieldError('statusError', 'Status is required');
            hasError = true;
        } else {
            const validStatuses = ['open', 'in_progress', 'closed'];
            if (!validStatuses.includes(status)) {
                showTicketFieldError('statusError', 'Invalid status value');
                hasError = true;
            }
        }

        if (description && description.length > 500) {
            showTicketFieldError('descriptionError', 'Description must not exceed 500 characters');
            hasError = true;
        }

        if (hasError) {
            return false;
        }

        // Create or update ticket
        const ticketData = { title, description, status, priority };

        if (ticketId) {
            // Update existing ticket
            updateTicket(ticketId, ticketData);
            showToast('Ticket updated successfully', 'success');
        } else {
            // Create new ticket
            createTicket(ticketData);
            showToast('Ticket created successfully', 'success');
            clearDraft();
        }

        // Reload tickets
        loadAndRenderTickets();

        // Close modal
        closeModal('createTicketModal');

        // Reset form
        ticketForm.reset();
        document.getElementById('ticketId').value = '';
    });

    // Delete form
    deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const ticketId = document.getElementById('deleteTicketId').value;

        if (deleteTicket(ticketId)) {
            showToast('Ticket deleted successfully', 'success');
            loadAndRenderTickets();
            closeModal('deleteModal');
        } else {
            showToast('Failed to delete ticket', 'error');
        }
    });
}

// Edit ticket
function editTicket(ticketId) {
    const ticket = getTicketById(ticketId);

    if (!ticket) {
        alert('Ticket not found');
        return;
    }

    document.getElementById('modalTitle').textContent = 'Edit Ticket';
    document.getElementById('ticketForm').action = '/tickets/update';
    document.getElementById('ticketId').value = ticket.id;
    document.getElementById('title').value = ticket.title;
    document.getElementById('description').value = ticket.description || '';
    document.getElementById('status').value = ticket.status;
    document.getElementById('priority').value = ticket.priority || 'medium';

    openModal('createTicketModal');
    setTimeout(() => lucide.createIcons(), 100);
}

// Confirm delete ticket
function confirmDeleteTicket(ticketId) {
    document.getElementById('deleteTicketId').value = ticketId;
    openModal('deleteModal');
}

// Reset form for creating new ticket
function resetTicketForm() {
    document.getElementById('modalTitle').textContent = 'Create New Ticket';
    document.getElementById('ticketForm').action = '/tickets/create';
    document.getElementById('ticketForm').reset();
    document.getElementById('ticketId').value = '';
    restoreDraft();
}

// Search functionality
function searchTickets() {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.toLowerCase();
    const tickets = document.querySelectorAll('.ticket-item');
    const grid = document.getElementById('ticketsGrid');
    const noResults = document.getElementById('noResults');
    const emptyState = document.getElementById('emptyState');
    let visibleCount = 0;

    // Save search query to localStorage
    localStorage.setItem(PAGE_STORAGE_KEYS.SEARCH_QUERY, searchInput.value);

    tickets.forEach(ticket => {
        const title = ticket.getAttribute('data-title');
        const description = ticket.getAttribute('data-description');
        const status = ticket.getAttribute('data-status');

        if (title.includes(query) || description.includes(query) || status.includes(query)) {
            ticket.classList.remove('hidden');
            visibleCount++;
        } else {
            ticket.classList.add('hidden');
        }
    });

    if (visibleCount === 0 && query !== '') {
        grid.classList.add('hidden');
        noResults.classList.remove('hidden');
        emptyState.classList.add('hidden');
    } else if (visibleCount === 0) {
        grid.classList.add('hidden');
        noResults.classList.add('hidden');
        emptyState.classList.remove('hidden');
    } else {
        grid.classList.remove('hidden');
        noResults.classList.add('hidden');
        emptyState.classList.add('hidden');
    }

    setTimeout(() => lucide.createIcons(), 100);
}

// Restore search query from localStorage
function restoreSearchQuery() {
    const savedQuery = localStorage.getItem(PAGE_STORAGE_KEYS.SEARCH_QUERY);
    const searchInput = document.getElementById('searchInput');

    if (savedQuery && searchInput) {
        searchInput.value = savedQuery;
        searchTickets();
    }
}

// Auto-save draft ticket to localStorage
function setupAutoSave() {
    const formFields = ['title', 'description', 'status', 'priority'];

    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', saveDraft);
        }
    });
}

// Save draft to localStorage
function saveDraft() {
    const ticketId = document.getElementById('ticketId').value;

    // Only auto-save for new tickets, not edits
    if (ticketId) return;

    const draft = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        status: document.getElementById('status').value,
        priority: document.getElementById('priority').value,
        timestamp: new Date().toISOString()
    };

    // Only save if there's actual content
    if (draft.title || draft.description) {
        localStorage.setItem(PAGE_STORAGE_KEYS.DRAFT_TICKET, JSON.stringify(draft));
    }
}

// Restore draft from localStorage
function restoreDraft() {
    const savedDraft = localStorage.getItem(PAGE_STORAGE_KEYS.DRAFT_TICKET);

    if (savedDraft) {
        try {
            const draft = JSON.parse(savedDraft);

            // Check if draft is less than 24 hours old
            const draftAge = Date.now() - new Date(draft.timestamp).getTime();
            const twentyFourHours = 24 * 60 * 60 * 1000;

            if (draftAge < twentyFourHours) {
                document.getElementById('title').value = draft.title || '';
                document.getElementById('description').value = draft.description || '';
                document.getElementById('status').value = draft.status || '';
                document.getElementById('priority').value = draft.priority || 'medium';
            } else {
                clearDraft();
            }
        } catch (e) {
            console.error('Error restoring draft:', e);
            clearDraft();
        }
    }
}

// Clear draft from localStorage
function clearDraft() {
    localStorage.removeItem(PAGE_STORAGE_KEYS.DRAFT_TICKET);
}

// Show ticket field error
function showTicketFieldError(fieldId, message) {
    const errorElement = document.getElementById(fieldId);
    errorElement.textContent = message;
    errorElement.classList.remove('hidden');
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-white text-sm border rounded-lg px-4 py-2 max-w-[320px] w-full shadow-lg z-50 animate-slide-in';
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <i data-lucide="${type === 'success' ? 'circle-check' : 'octagon-x'}" class="w-4 h-4"></i>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    lucide.createIcons();

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Override openModal for create ticket to reset form
const originalOpenModal = window.openModal;
window.openModal = function(modalId) {
    if (modalId === 'createTicketModal' && !document.getElementById('ticketId').value) {
        resetTicketForm();
    }
    originalOpenModal(modalId);
    setTimeout(() => lucide.createIcons(), 100);
};
