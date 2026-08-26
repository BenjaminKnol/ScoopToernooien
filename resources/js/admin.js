function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function showToast(message, type = 'success') {
    const el = document.createElement('div');
    el.className = [
        'fixed top-2 inset-x-0 z-50 mx-auto max-w-3xl rounded-xl border p-3 text-center text-sm shadow-lg',
        type === 'error'
            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-400/30 dark:bg-red-400/10 dark:text-red-300'
            : 'border-green-200 bg-green-50 text-green-700 dark:border-green-400/30 dark:bg-green-400/10 dark:text-green-300',
    ].join(' ');
    el.style.left = 'env(safe-area-inset-left)';
    el.style.right = 'env(safe-area-inset-right)';
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

async function ajaxSubmit(form) {
    const method = (form.querySelector('input[name="_method"]')?.value || form.method || 'POST').toUpperCase();
    const fetchMethod = method === 'GET' ? 'GET' : 'POST';

    const res = await fetch(form.action, {
        method: fetchMethod,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: fetchMethod === 'GET' ? null : new FormData(form),
        credentials: 'same-origin',
    });

    await res.text();

    return res.ok;
}

function removeTarget(form) {
    const target = form.dataset.removeTarget;
    if (!target) return;

    const [, selector] = target.split(':');
    form.closest(selector)?.remove();
}

async function handleAjaxForm(form) {
    try {
        const ok = await ajaxSubmit(form);

        showToast(
            ok ? (form.dataset.toastSuccess || 'Saved successfully') : (form.dataset.toastError || 'Failed to save'),
            ok ? 'success' : 'error'
        );

        if (ok && form.dataset.ajax === 'delete') {
            removeTarget(form);
        }

        return ok;
    } catch (err) {
        showToast(form.dataset.toastError || 'Failed to save', 'error');

        return false;
    }
}

function initAjaxForms(root) {
    root.querySelectorAll('form[data-ajax]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            handleAjaxForm(form);
        });
    });
}

function initAutosaveSelects(root) {
    root.querySelectorAll('[data-autosave]').forEach((select) => {
        select.addEventListener('change', () => {
            const form = select.closest('form');
            if (form) handleAjaxForm(form);
        });
    });
}

function readScorePair(form) {
    const t1 = form.querySelector('[name="team_1_score"]')?.value ?? '';
    const t2 = form.querySelector('[name="team_2_score"]')?.value ?? '';
    return (t1 !== '' && t2 !== '') ? `${t1}-${t2}` : '';
}

function initScoreConfirmModals(root) {
    const modalName = 'confirm-score';
    let pendingForm = null;

    const teams = document.getElementById('confirm-score-teams');
    const current = document.getElementById('confirm-score-current');
    const team1Label = document.getElementById('confirm-score-team1-label');
    const team1Value = document.getElementById('confirm-score-team1');
    const team2Label = document.getElementById('confirm-score-team2-label');
    const team2Value = document.getElementById('confirm-score-team2');
    const newScore = document.getElementById('confirm-score-new');
    const submitButton = document.getElementById('confirm-score-submit');

    if (!submitButton) return;

    root.querySelectorAll('form[data-confirm="score"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            pendingForm = form;

            const team1 = form.dataset.confirmTeam1 || '';
            const team2 = form.dataset.confirmTeam2 || '';

            teams.textContent = `${team1} vs ${team2}`;
            current.textContent = form.dataset.confirmCurrentFinal || '—';
            team1Label.textContent = `${team1} submission`;
            team1Value.textContent = form.dataset.confirmTeam1Submission || '—';
            team2Label.textContent = `${team2} submission`;
            team2Value.textContent = form.dataset.confirmTeam2Submission || '—';
            newScore.textContent = readScorePair(form);

            window.Flux?.modal(modalName).show();
        });
    });

    submitButton.addEventListener('click', async () => {
        window.Flux?.modal(modalName).close();

        if (!pendingForm) return;

        const form = pendingForm;
        const statusTargetId = form.dataset.confirmStatusTarget;
        const scoreValue = readScorePair(form);
        pendingForm = null;

        const ok = await handleAjaxForm(form);

        if (ok && statusTargetId) {
            const target = document.getElementById(statusTargetId);
            if (target) target.textContent = `Final: ${scoreValue}`;
        }
    });
}

export function initAdmin() {
    const root = document.querySelector('[data-admin]');
    if (!root) return;

    initAjaxForms(root);
    initAutosaveSelects(root);
    initScoreConfirmModals(root);
}

initAdmin();
