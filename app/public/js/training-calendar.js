document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('training-calendar');
    const loadErrorEl = document.getElementById('trainingCalendarLoadError');

    const showLoadError = (message) => {
        if (!loadErrorEl) return;
        if (!message) {
            loadErrorEl.classList.add('d-none');
            loadErrorEl.textContent = '';
            return;
        }
        loadErrorEl.textContent = message;
        loadErrorEl.classList.remove('d-none');
    };

    if (!calendarEl) {
        showLoadError('Chýba element pre kalendár (#training-calendar).');
        return;
    }

    if (typeof FullCalendar === 'undefined' || typeof FullCalendar.Calendar !== 'function') {
        showLoadError('Kalendár sa nenačítal: knižnica FullCalendar nie je dostupná alebo je načítaná v nekompatibilnej verzii.');
        return;
    }

    const eventsUrl = window.trainingEventsUrl || '/training-events';
    const registerRouteTemplate = window.trainingRegisterRouteTemplate || '';
    const unregisterRouteTemplate = window.trainingUnregisterRouteTemplate || '';
    const canRegisterForTrainings = Boolean(window.canRegisterForTrainings);
    const isAdminUser = Boolean(window.isAdminUser);
    const adminTrainingEditRouteTemplate = window.adminTrainingEditRouteTemplate || '';

    const showError = (message) => {
        const errEl = document.getElementById('trainingEventError');
        if (!errEl) return;
        if (!message) {
            errEl.classList.add('d-none');
            errEl.textContent = '';
            return;
        }
        errEl.textContent = message;
        errEl.classList.remove('d-none');
    };

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'sk',
        buttonText: {
            today: 'Dnes',
            month: 'Mesiac',
            week: 'Týždeň',
            day: 'Deň',
            list: 'Zoznam',
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },

        // In month view, make events render as full "block" bars (not dot/list style)
        eventDisplay: 'block',
        displayEventTime: true,

        eventClassNames: function (arg) {
            const ext = arg.event.extendedProps || {};
            const isPast = Boolean(ext.is_past);
            const isActive = Boolean(ext.is_active ?? true);
            const isRegistered = Boolean(ext.is_registered);

            const classes = [];
            if (isPast || !isActive) classes.push('gym-event-not-current');
            if (isRegistered) classes.push('gym-event-registered');
            return classes;
        },

        events: function (fetchInfo, successCallback, failureCallback) {
            fetch(eventsUrl + '?start=' + encodeURIComponent(fetchInfo.startStr) + '&end=' + encodeURIComponent(fetchInfo.endStr), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(async (res) => {
                    if (!res.ok) {
                        const text = await res.text();
                        throw new Error('HTTP ' + res.status + ': ' + text.slice(0, 200));
                    }
                    return res.json();
                })
                .then((data) => {
                    showLoadError(null);
                    successCallback(data);
                })
                .catch((err) => {
                    showLoadError('Nepodarilo sa načítať tréningy: ' + (err?.message || err));
                    failureCallback(err);
                });
        },
        eventDidMount: function (info) {
            // Keep inline painting as a fallback.
            const ext = info.event.extendedProps || {};
            const isPast = Boolean(ext.is_past);
            const isActive = Boolean(ext.is_active ?? true);
            const isRegistered = Boolean(ext.is_registered);

            let bg = null;
            let border = null;
            let text = null;

            if (isPast || !isActive) {
                bg = '#e9ecef';
                border = '#ced4da';
                text = '#6c757d';
            } else if (isRegistered) {
                bg = '#198754';
                border = '#198754';
                text = '#ffffff';
            }

            if (bg) {
                // Paint multiple possible elements because FullCalendar renders differently in dayGrid month.
                const els = [
                    info.el,
                    info.el?.querySelector?.('.fc-daygrid-event'),
                    info.el?.querySelector?.('.fc-event-main'),
                    info.el?.querySelector?.('.fc-event-main-frame'),
                    info.el?.querySelector?.('.fc-list-event'),
                    info.el?.querySelector?.('.fc-daygrid-event-harness'),
                ].filter(Boolean);

                els.forEach((el) => {
                    try {
                        el.style.backgroundColor = bg;
                        el.style.borderColor = border || bg;
                        if (text) el.style.color = text;
                    } catch (_) {
                        // ignore
                    }
                });

                // If FC renders a dot (list-item), color the dot too
                const dot = info.el?.querySelector?.('.fc-daygrid-event-dot');
                if (dot) {
                    dot.style.borderColor = bg;
                    dot.style.backgroundColor = bg;
                }
            }
        },
        eventClick: function (info) {
            const event = info.event;
            const extended = event.extendedProps || {};

            const titleEl = document.getElementById('trainingEventTitle');
            const descEl = document.getElementById('trainingEventDescription');
            const creatorEl = document.getElementById('trainingEventCreator');
            const startEl = document.getElementById('trainingEventStart');
            const endEl = document.getElementById('trainingEventEnd');
            const capEl = document.getElementById('trainingEventCapacity');
            const priceEl = document.getElementById('trainingEventPrice');
            const regEl = document.getElementById('trainingEventRegistered');
            const attendeesEl = document.getElementById('trainingEventAttendees');

            const price = Number(extended.price ?? 0);

            if (titleEl) titleEl.textContent = event.title || 'Tréning';
            if (descEl) descEl.textContent = extended.description || '';
            if (startEl) startEl.textContent = event.start?.toLocaleString('sk-SK') ?? '';
            if (endEl) endEl.textContent = event.end?.toLocaleString('sk-SK') ?? '';
            if (capEl) capEl.textContent = extended.capacity ?? '-';
            if (priceEl) priceEl.textContent = Number.isFinite(price) ? String(price) : '0';
            if (regEl) regEl.textContent = extended.registered ?? '-';

            const creatorName = extended?.creator?.name ?? '';
            if (creatorEl) creatorEl.textContent = creatorName || '-';

            if (attendeesEl) {
                attendeesEl.innerHTML = '';
                const attendees = Array.isArray(extended.attendees) ? extended.attendees : [];
                if (attendees.length === 0) {
                    const li = document.createElement('li');
                    li.className = 'text-muted';
                    li.textContent = 'Zatiaľ nikto.';
                    attendeesEl.appendChild(li);
                } else {
                    attendees.forEach((a) => {
                        const li = document.createElement('li');
                        li.textContent = a?.name ?? 'Používateľ';
                        attendeesEl.appendChild(li);
                    });
                }
            }

            showError(null);

            const registerBtn = document.getElementById('trainingEventRegisterButton');
            const registerForm = document.getElementById('trainingRegisterForm');
            const unregisterBtn = document.getElementById('trainingEventUnregisterButton');
            const unregisterForm = document.getElementById('trainingUnregisterForm');
            const adminEditLink = document.getElementById('trainingEventAdminEditLink');

            const capacity = Number(extended.capacity ?? 0);
            const registered = Number(extended.registered ?? 0);
            const isFull = capacity > 0 && registered >= capacity;
            const isRegistered = Boolean(extended.is_registered);
            const isPast = Boolean(extended.is_past);
            const isActive = Boolean(extended.is_active ?? true);
            const isNotCurrent = isPast || !isActive;

            // If the current user cannot register (admin/trainer), hide actions and show message.
            if (!canRegisterForTrainings) {
                if (registerForm) registerForm.classList.add('d-none');
                if (unregisterForm) unregisterForm.classList.add('d-none');
                showError('Na tréningy sa môže prihlasovať iba bežný používateľ.');
            } else {
                if (registerForm) registerForm.classList.remove('d-none');
                // unregisterForm is toggled below based on isRegistered

                // Register button
                if (registerBtn) {
                    registerBtn.disabled = !registerRouteTemplate || isFull || isRegistered || isNotCurrent;
                    registerBtn.textContent = isNotCurrent
                        ? 'Tréning nie je aktuálny'
                        : (isFull
                            ? 'Tréning je plný'
                            : (isRegistered
                                ? 'Už si prihlásený'
                                : (price > 0 ? `Kúpiť tréning (${price} kreditov)` : 'Prihlásiť sa')));
                }
                if (registerForm && registerRouteTemplate) {
                    registerForm.action = registerRouteTemplate.replace('__ID__', String(event.id));
                }

                // Unregister button
                if (unregisterForm && unregisterRouteTemplate) {
                    unregisterForm.action = unregisterRouteTemplate.replace('__ID__', String(event.id));
                }
                if (unregisterForm) {
                    // show only when registered
                    unregisterForm.classList.toggle('d-none', !isRegistered);
                }
                if (unregisterBtn) {
                    unregisterBtn.disabled = !unregisterRouteTemplate || !isRegistered;
                }
            }

            if (adminEditLink) {
                if (isAdminUser && adminTrainingEditRouteTemplate) {
                    adminEditLink.href = adminTrainingEditRouteTemplate.replace('__ID__', String(event.id));
                    adminEditLink.classList.remove('d-none');
                } else {
                    adminEditLink.classList.add('d-none');
                    adminEditLink.href = '#';
                }
            }

            const modalEl = document.getElementById('trainingEventModal');
            if (modalEl && window.bootstrap) {
                const modal = new window.bootstrap.Modal(modalEl);
                modal.show();
            }
        },
    });

    const form = document.getElementById('trainingRegisterForm');

    // Helper: refresh credits from server and update both desktop and mobile badges
    async function refreshCreditsBadgeFromServer() {
        try {
            const res = await fetch('/me/credits', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const payload = await res.json().catch(() => null);
            if (!payload || typeof payload.credits === 'undefined') return;

            console.debug('refreshCreditsBadgeFromServer: credits=', payload.credits);

            const desktop = document.getElementById('userCreditsBadge');
            const mobile = document.getElementById('userCreditsBadgeMobile');
            const text = `Kredity: ${payload.credits}`;
            if (desktop) desktop.textContent = text;
            if (mobile) mobile.textContent = text;

            // Broadcast the new credits so other parts of the UI can react
            try {
                window.dispatchEvent(new CustomEvent('credits:updated', { detail: { credits: payload.credits } }));
            } catch (e) {
                // ignore
            }
        } catch (e) {
            // ignore network errors
        }
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            showError(null);
            const btn = document.getElementById('trainingEventRegisterButton');
            if (btn) btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (form.querySelector('input[name="_token"]')?.value ?? ''),
                },
                credentials: 'same-origin',
            })
                .then(async (res) => {
                    if (res.ok) {
                        return { ok: true, data: null };
                    }

                    // Laravel might return JSON or text
                    let payload;
                    try {
                        payload = await res.json();
                    } catch {
                        payload = { message: await res.text() };
                    }

                    const msg = payload?.message || (payload?.error ? String(payload.error) : null) || 'Nepodarilo sa prihlásiť.';
                    return { ok: false, status: res.status, message: msg };
                })
                .then((result) => {
                    if (!result.ok) {
                        showError(result.message || 'Nepodarilo sa prihlásiť.');
                        if (btn) btn.disabled = false;
                        return;
                    }

                    // refresh events so event changes color to green for this user
                    calendar.refetchEvents();

                    // Update credits badges (desktop + mobile) from server so both stay in sync
                    refreshCreditsBadgeFromServer();

                    // close modal
                    const modalEl = document.getElementById('trainingEventModal');
                    if (modalEl && window.bootstrap) {
                        const inst = window.bootstrap.Modal.getInstance(modalEl);
                        if (inst) inst.hide();
                    }
                })
                .catch((err) => {
                    showError(err?.message || 'Nepodarilo sa prihlásiť.');
                    if (btn) btn.disabled = false;
                });
        });
    }

    const unregisterForm = document.getElementById('trainingUnregisterForm');
    if (unregisterForm) {
        unregisterForm.addEventListener('submit', function (e) {
            e.preventDefault();

            showError(null);
            const btn = document.getElementById('trainingEventUnregisterButton');
            if (btn) btn.disabled = true;

            fetch(unregisterForm.action, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (unregisterForm.querySelector('input[name="_token"]')?.value ?? ''),
                },
                credentials: 'same-origin',
            })
                .then(async (res) => {
                    if (res.ok) {
                        return { ok: true };
                    }

                    let payload;
                    try {
                        payload = await res.json();
                    } catch {
                        payload = { message: await res.text() };
                    }

                    const msg = payload?.message || 'Nepodarilo sa odhlásiť.';
                    return { ok: false, status: res.status, message: msg };
                })
                .then((result) => {
                    if (!result.ok) {
                        showError(result.message || 'Nepodarilo sa odhlásiť.');
                        if (btn) btn.disabled = false;
                        return;
                    }

                    // refresh events so event changes color back to default (blue)
                    calendar.refetchEvents();

                    // Update credits badges (desktop + mobile) from server so both stay in sync
                    refreshCreditsBadgeFromServer();

                    // close modal
                    const modalEl = document.getElementById('trainingEventModal');
                    if (modalEl && window.bootstrap) {
                        const inst = window.bootstrap.Modal.getInstance(modalEl);
                        if (inst) inst.hide();
                    }
                })
                .catch((err) => {
                    showError(err?.message || 'Nepodarilo sa odhlásiť.');
                    if (btn) btn.disabled = false;
                });
        });
    }

    calendar.render();
});
