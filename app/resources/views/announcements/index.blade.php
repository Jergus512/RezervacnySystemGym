@extends('layouts.app')

@section('title', 'Oznamy')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Oznamy</h1>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div id="announcementsError" class="alert alert-danger d-none"></div>

    {{-- Loader: zobrazí sa len keď JS beží (default d-none, JS ho prepne) --}}
    <div id="announcementsLoading" class="alert alert-secondary d-none">Načítavam oznamy…</div>

    <div id="announcementsList">
        {{-- Fallback bez JS: server-side render --}}
        @if($announcements->isEmpty())
            <div class="alert alert-secondary">Momentálne nie sú žiadne aktívne oznamy.</div>
        @else
            <div class="vstack gap-3">
                @foreach($announcements as $a)
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1 text-brand-orange">{{ $a->title ?: 'Oznam' }}</h2>
                                    <div class="text-muted small">
                                        @if($a->active_from || $a->active_to)
                                            Platí
                                            @if($a->active_from) od {{ $a->active_from->format('d.m.Y H:i') }} @endif
                                            @if($a->active_to) do {{ $a->active_to->format('d.m.Y H:i') }} @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="announcement-content" style="white-space: pre-wrap;">{{ $a->content }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        (function () {
            const listEl = document.getElementById('announcementsList');
            const errEl = document.getElementById('announcementsError');
            const loadingEl = document.getElementById('announcementsLoading');

            // Polling fallback: ak nevieme presný najbližší čas, refreshujeme každých 10s
            const POLL_MS = 10_000;

            let pollTimer = null;
            let nextExactTimer = null;

            function escapeHtml(str) {
                return (str ?? '').toString()
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function formatDate(iso) {
                if (!iso) return null;
                const d = new Date(iso);
                if (Number.isNaN(d.getTime())) return null;
                const pad = (n) => String(n).padStart(2, '0');
                return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            }

            function render(announcements) {
                errEl.classList.add('d-none');
                errEl.textContent = '';

                if (!announcements || announcements.length === 0) {
                    listEl.innerHTML = '<div class="alert alert-secondary">Momentálne nie sú žiadne aktívne oznamy.</div>';
                    return;
                }

                const cards = announcements.map(a => {
                    const title = escapeHtml(a.title || 'Oznam');
                    const from = formatDate(a.active_from);
                    const to = formatDate(a.active_to);

                    let validity = '';
                    if (from || to) {
                        validity = '<div class="text-muted small">Platí'
                            + (from ? ` od ${escapeHtml(from)}` : '')
                            + (to ? ` do ${escapeHtml(to)}` : '')
                            + '</div>';
                    }

                    return `
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h2 class="h5 mb-1 text-brand-orange">${title}</h2>
                                        ${validity}
                                    </div>
                                </div>
                                <hr>
                                <div class="announcement-content" style="white-space: pre-wrap;">${escapeHtml(a.content || '')}</div>
                            </div>
                        </div>
                    `;
                }).join('');

                listEl.innerHTML = `<div class="vstack gap-3">${cards}</div>`;
            }

            function scheduleNextExactRefresh(serverNowIso, data) {
                // Zruš starý timer
                if (nextExactTimer) {
                    clearTimeout(nextExactTimer);
                    nextExactTimer = null;
                }

                const serverNow = new Date(serverNowIso);
                if (Number.isNaN(serverNow.getTime())) return;

                // Nájdeme najbližší active_from, ktorý je v budúcnosti (podľa serverového času)
                let nextStart = null;
                for (const a of (data ?? [])) {
                    if (!a.active_from) continue;
                    const d = new Date(a.active_from);
                    if (Number.isNaN(d.getTime())) continue;
                    if (d.getTime() > serverNow.getTime() && (!nextStart || d.getTime() < nextStart.getTime())) {
                        nextStart = d;
                    }
                }

                if (!nextStart) return;

                // Rozdiel v ms
                const delay = nextStart.getTime() - serverNow.getTime();

                // Bezpečnostný limit: ak je to veľmi ďaleko, spoliehame sa na polling
                if (delay <= 0 || delay > 6 * 60 * 60 * 1000) return;

                // Pridáme malý buffer (1s), aby už oznam určite spadal do aktívneho rozsahu
                nextExactTimer = setTimeout(() => {
                    loadAnnouncements({ silent: true });
                }, delay + 1000);
            }

            async function loadAnnouncements(opts = {}) {
                const { silent = false } = opts;

                if (!silent) {
                    loadingEl.classList.remove('d-none');
                }

                try {
                    const url = new URL('{{ route('announcements.current') }}', window.location.origin);
                    url.searchParams.set('_', String(Date.now()));

                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }

                    const json = await res.json();
                    render(json.data);

                    // naplánuj presné ďalšie načítanie podľa server času
                    scheduleNextExactRefresh(json.server_now, json.data);
                } catch (e) {
                    // necháme fallback obsah z PHP, iba zobrazíme chybu
                    errEl.classList.remove('d-none');
                    errEl.textContent = 'Nepodarilo sa načítať oznamy. Skús obnoviť stránku.';
                } finally {
                    if (!silent) {
                        loadingEl.classList.add('d-none');
                    }
                }
            }

            function startPolling() {
                if (pollTimer) return;
                pollTimer = setInterval(() => {
                    loadAnnouncements({ silent: true });
                }, POLL_MS);
            }

            function stopTimers() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                if (nextExactTimer) {
                    clearTimeout(nextExactTimer);
                    nextExactTimer = null;
                }
            }

            // pri odchode zo stránky ukončíme timery
            window.addEventListener('beforeunload', stopTimers);

            // 1) prvé načítanie hneď
            loadAnnouncements();
            // 2) polling, aby oznamy nabehli aj bez refreshu (napr. keď admin pridá oznam v inom okne)
            startPolling();
        })();
    </script>
@endsection
