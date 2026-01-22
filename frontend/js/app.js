
const API_BASE = window.location.port === '5500'
    ? 'http://localhost/study-group-and-event/backend/api'
    : '../backend/api';

let currentUser = null;

function checkAuth() {
    const userData = localStorage.getItem('user');
    if (userData) {
        currentUser = JSON.parse(userData);
        updateNavForUser();
    } else {
        updateNavForGuest();
    }
}

function updateNavForUser() {
    document.getElementById('loggedOutMenu')?.classList.add('hidden');
    document.getElementById('loggedInMenu')?.classList.remove('hidden');
    document.getElementById('loggedOutMenu')?.classList.add('hidden');
    document.getElementById('loggedInMenu')?.classList.remove('hidden');

    const nameEl = document.getElementById('userName');
    if (nameEl) {
        nameEl.innerHTML = `<a href="profile.html" style="color: inherit; text-decoration: none;">${currentUser.name}</a>`;
    }

    const menu = document.getElementById('loggedInMenu');
    if (menu) {

        const userNameEl = document.getElementById('userName');
        if (userNameEl) {
            userNameEl.textContent = currentUser.name;
            // Make parent clickable if not already?
            if (userNameEl.parentElement.tagName !== 'A') {
                userNameEl.parentElement.innerHTML = `Hello, <a href="profile.html" style="font-weight:700; color:inherit;">${currentUser.name}</a>`;
            }
        }
    }

    document.getElementById('navCreateEvent')?.classList.remove('hidden');
    document.getElementById('navCalendar')?.classList.remove('hidden');
    document.getElementById('mobileCreateEvent')?.classList.remove('hidden');
    document.getElementById('mobileCalendar')?.classList.remove('hidden');
    document.getElementById('mobileLogout')?.classList.remove('hidden');
    document.getElementById('mobileLogin')?.classList.add('hidden');
    document.getElementById('mobileSignup')?.classList.add('hidden');

    if (currentUser.role === 'admin') {
        document.getElementById('navAdmin')?.classList.remove('hidden');
        document.getElementById('mobileAdmin')?.classList.remove('hidden');
    }

    if (currentUser.department) {
        document.getElementById('departmentSection')?.classList.remove('hidden');
        const deptTitle = document.getElementById('departmentTitle');
        if (deptTitle) deptTitle.textContent = `🎓 Recommended for ${currentUser.department}`;
    }
}

function updateNavForGuest() {
    document.getElementById('loggedOutMenu')?.classList.remove('hidden');
    document.getElementById('loggedInMenu')?.classList.add('hidden');
}

async function login(email, password) {
    try {
        const response = await fetch(`${API_BASE}/auth.php?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Server returned non-JSON:', text);
            return { success: false, message: 'Server error: ' + text.substring(0, 100) };
        }

        if (data.success) {
            currentUser = data.data;
            localStorage.setItem('user', JSON.stringify(currentUser));
            return { success: true };
        }
        return { success: false, message: data.message };
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Network or Server Error. Ensure you are accessing via http://localhost' };
    }
}

async function signup(userData) {
    try {
        const response = await fetch(`${API_BASE}/auth.php?action=register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Server returned non-JSON:', text);
            return { success: false, message: 'Server error: ' + text.substring(0, 100) };
        }

        return data;
    } catch (error) {
        console.error('Signup error:', error);
        return { success: false, message: 'Network or Server Error. Ensure you are accessing via http://localhost' };
    }
}

function logout() {
    currentUser = null;
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

// ============================================
// Events API
// ============================================

async function fetchEvents(category = '', search = '') {
    try {
        let url = `${API_BASE}/events.php?status=approved`;
        if (category) url += `&category=${encodeURIComponent(category)}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        const response = await fetch(url);
        const data = await response.json();
        return data.success ? data.data : [];
    } catch (error) {
        console.error('Fetch events error:', error);
        return [];
    }
}

async function toggleJoinEvent(eventId, action) {
    if (!currentUser) {
        window.location.href = 'login.html';
        return;
    }
    try {
        const response = await fetch(`${API_BASE}/join_event.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId, action, user_id: currentUser.id })
        });
        return await response.json();
    } catch (error) {
        console.error('Join event error:', error);
        return { success: false };
    }
}

// ============================================
// UI Rendering
// ============================================

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card';
    card.onclick = () => viewEvent(event.id);

    const eventDate = new Date(event.event_date);
    const formattedDate = eventDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const formattedTime = event.event_time
        ? new Date(`2000-01-01T${event.event_time}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
        : '';

    const imageUrl = event.image_path
        ? `../backend/uploads/${event.image_path}`
        : 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=400&h=200&fit=crop';

    const categoryClass = event.category.toLowerCase();

    card.innerHTML = `
        <div class="card-image">
            <img src="${imageUrl}" alt="${escapeHtml(event.title)}">
            <span class="card-category ${categoryClass}">${escapeHtml(event.category)}</span>
        </div>
        <div class="card-content">
            <div class="card-date">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>${formattedDate}${formattedTime ? ' • ' + formattedTime : ''}</span>
            </div>
            <h3 class="card-title">${escapeHtml(event.title)}</h3>
            <div class="card-location">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>${escapeHtml(event.location || 'TBD')}</span>
            </div>
            <div class="card-footer">
                <span class="card-creator">by ${escapeHtml(event.creator_name || 'Unknown')}</span>
                <span class="card-action">View Details →</span>
            </div>
        </div>
    `;
    return card;
}

function viewEvent(eventId) {
    window.location.href = `event_details.html?id=${eventId}`;
}

function renderEvents(container, events) {
    const el = document.getElementById(container);
    if (!el) return;

    el.innerHTML = '';
    if (events.length === 0) {
        el.innerHTML = `
            <div class="empty-state" style="width: 100%;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3>No events found</h3>
                <p>Check back later!</p>
            </div>
        `;
        return;
    }
    events.forEach(event => el.appendChild(createEventCard(event)));
}
// Page Initialization


async function initHomePage() {
    checkAuth();
    const events = await fetchEvents();
    const today = new Date().toISOString().split('T')[0];

    const todayEvents = events.filter(e => e.event_date === today);
    renderEvents('todayEvents', todayEvents);
    if (todayEvents.length === 0) document.getElementById('todaySection')?.classList.add('hidden');

    if (currentUser?.department) {
        const deptEvents = events.filter(e => e.creator_department === currentUser.department);
        renderEvents('departmentEvents', deptEvents.slice(0, 8));
    }

    renderEvents('trendingEvents', events.slice(0, 6));
    renderEvents('studyGroups', events.filter(e => e.category === 'Study').slice(0, 8));
    renderEvents('allEvents', events);

    setupSearch();
    setupCategoryPills();
}

function setupSearch() {
    const form = document.getElementById('searchForm');
    const input = document.getElementById('searchInput');
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const query = input.value.trim();
        if (query) await performSearch(query);
    });
}

async function performSearch(query) {
    const events = await fetchEvents('', query);
    document.getElementById('searchResults')?.classList.remove('hidden');
    document.getElementById('searchResultsTitle').textContent = `Results for "${query}"`;
    document.getElementById('searchResultsCount').textContent = `${events.length} events found`;
    renderEvents('searchResultsGrid', events);
    document.getElementById('searchResults')?.scrollIntoView({ behavior: 'smooth' });
}

function setupCategoryPills() {
    const pills = document.querySelectorAll('.category-pill');
    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            filterByCategory(pill.dataset.category);
        });
    });
}

async function filterByCategory(category) {
    const events = await fetchEvents(category);
    if (category) {
        document.getElementById('searchResults')?.classList.remove('hidden');
        document.getElementById('searchResultsTitle').textContent = `${category} Events`;
        document.getElementById('searchResultsCount').textContent = `${events.length} events found`;
        renderEvents('searchResultsGrid', events);
    } else {
        document.getElementById('searchResults')?.classList.add('hidden');
        renderEvents('allEvents', events);
    }
}

// Mobile Menu & Logout


document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
    document.getElementById('mobileMenu')?.classList.toggle('active');
});

document.getElementById('logoutBtn')?.addEventListener('click', (e) => { e.preventDefault(); logout(); });
document.getElementById('mobileLogout')?.addEventListener('click', (e) => { e.preventDefault(); logout(); });

// ============================================
// Utilities
// ============================================

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getUrlParam(param) {
    return new URLSearchParams(window.location.search).get(param);
}

// ============================================
// Notifications
// ============================================

async function checkNotifications() {
    if (!currentUser) return;
    try {
        const response = await fetch(`${API_BASE}/notifications.php`, {
            headers: { 'x-user-id': currentUser.id }
        });
        const data = await response.json();
        if (data.success) {
            updateNotificationUI(data.data);
        }
    } catch (e) {
        console.error('Error fetching notifications:', e);
    }
}

function updateNotificationUI(notifications) {
    const uniqueId = 'notifBtnContainer';
    let container = document.getElementById(uniqueId);

    if (!container) {
        // Create bell icon if not exists
        container = document.createElement('div');
        container.id = uniqueId;
        container.style.display = 'inline-block';
        container.style.position = 'relative';
        container.style.marginRight = '1rem';
        container.style.cursor = 'pointer';

        container.innerHTML = `
            <svg style="width: 24px; height: 24px; color: var(--slate-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span id="notifBadge" style="position: absolute; top: -5px; right: -5px; background: var(--red-500); color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; display: none;">0</span>
        `;

        container.onclick = () => showNotificationsList(notifications);

        // Insert before Hello User
        const menu = document.getElementById('loggedInMenu');
        if (menu) menu.prepend(container);
    }

    // Update Badge
    const badge = document.getElementById('notifBadge');
    if (badge) {
        if (notifications.length > 0) {
            badge.textContent = notifications.length;
            badge.style.display = 'block';

            // Update click handler with fresh data
            container.onclick = () => showNotificationsList(notifications);
        } else {
            badge.style.display = 'none';
        }
    }
}

function showNotificationsList(notifications) {
    if (notifications.length === 0) {
        alert("No new notifications.");
        return;
    }

    let msg = "Unread Notifications:\n\n";
    notifications.forEach(n => {
        msg += `- ${n.message}\n`;
        // Mark as read in background
        markNotificationRead(n.id);
    });

    alert(msg);
    // Refresh to clear badge
    setTimeout(checkNotifications, 1000);
}

async function markNotificationRead(id) {
    try {
        fetch(`${API_BASE}/notifications.php`, {
            method: 'POST',
            headers: { 'x-user-id': currentUser.id },
            body: JSON.stringify({ id })
        });
    } catch (e) { }
}
// Auto-init


document.addEventListener('DOMContentLoaded', () => {
    const page = window.location.pathname.split('/').pop() || 'index.html';
    if (page === 'index.html' || page === '') initHomePage();
    else checkAuth();

    // Check notifications periodically (every 60s) and on load
    if (currentUser) {
        checkNotifications();
        setInterval(checkNotifications, 60000);
    }
});
