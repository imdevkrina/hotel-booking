@extends('layouts.app')

@section('title', 'Manage Discounts — Hotel Booking')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Discount Rules</h1>
            <p class="text-slate-500 text-sm mt-1">Click any value to edit inline. Changes save automatically.</p>
        </div>
        <button onclick="showAddForm()" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
            Add Rule
        </button>
    </div>

    <!-- Status banner -->
    <div id="statusBanner" class="hidden mb-4 rounded-xl px-4 py-3 text-sm font-medium"></div>

    <!-- Add Form -->
    <div id="addForm" class="hidden mb-6 bg-white rounded-2xl shadow-md border border-slate-100 p-6">
        <h3 class="text-sm font-bold text-slate-700 mb-4">Add New Discount Rule</h3>
        <form id="discountAddForm" novalidate>
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Type</label>
                <select id="newType" name="newType" onchange="onTypeChange()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                    <option value="long_stay">Long Stay</option>
                    <option value="last_minute">Last Minute</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Percentage (%)</label>
                <input type="number" id="newPct" name="newPct" value="10" step="0.01" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div id="minNightsGroup">
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Min Nights</label>
                <input type="number" id="newMinNights" name="newMinNights" placeholder="e.g. 3" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div id="daysBeforeGroup" class="hidden">
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Days Before</label>
                <input type="number" id="newDaysBefore" name="newDaysBefore" placeholder="e.g. 3" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Active</label>
                <select id="newActive" name="newActive" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition">Save</button>
                <button type="button" onclick="hideAddForm()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-4 py-2.5 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">Type</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">Percentage (%)</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">Min Nights</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">Days Before Check-in</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">Active</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide w-20">Actions</th>
                    </tr>
                </thead>
                <tbody id="discountBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
        <div id="emptyState" class="hidden py-16 text-center text-slate-400">
            <p class="text-lg font-medium">No discount rules found.</p>
            <p class="text-sm mt-1">Click "Add Rule" to create one.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const tbody = document.getElementById('discountBody');
    const banner = document.getElementById('statusBanner');
    const emptyEl = document.getElementById('emptyState');
    const addFormEl = document.getElementById('addForm');

    function headers() {
        return { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf };
    }

    function flash(msg, type = 'success') {
        banner.className = 'mb-4 rounded-xl px-4 py-3 text-sm font-medium ' +
            (type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700');
        banner.textContent = msg;
        banner.classList.remove('hidden');
        setTimeout(() => banner.classList.add('hidden'), 3000);
    }

    function typeLabel(t) {
        return t === 'long_stay' ? 'Long Stay' : 'Last Minute';
    }

    // --- Load ---
    async function loadDiscounts() {
        const res = await fetch('/api/discounts', { headers: headers() });
        const rows = await res.json();
        tbody.innerHTML = '';
        if (!rows.length) { emptyEl.classList.remove('hidden'); return; }
        emptyEl.classList.add('hidden');
        rows.forEach(r => tbody.appendChild(buildRow(r)));
    }

    function buildRow(r) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/50 transition-colors';
        tr.dataset.id = r.id;
        tr.innerHTML = `
            <td class="px-5 py-3">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full ${
                    r.type === 'long_stay' ? 'bg-purple-50 text-purple-700' : 'bg-amber-50 text-amber-700'
                }">${typeLabel(r.type)}</span>
            </td>
            <td class="px-5 py-3 text-right">
                <span class="editable-cell cursor-pointer hover:bg-brand-50 rounded px-2 py-1 -mx-2 transition" data-field="percentage" data-id="${r.id}">${Number(r.percentage)}</span>
            </td>
            <td class="px-5 py-3 text-right">
                <span class="editable-cell cursor-pointer hover:bg-brand-50 rounded px-2 py-1 -mx-2 transition" data-field="min_nights" data-id="${r.id}">${r.min_nights ?? '—'}</span>
            </td>
            <td class="px-5 py-3 text-right">
                <span class="editable-cell cursor-pointer hover:bg-brand-50 rounded px-2 py-1 -mx-2 transition" data-field="days_before_checkin" data-id="${r.id}">${r.days_before_checkin ?? '—'}</span>
            </td>
            <td class="px-5 py-3 text-center">
                <button onclick="toggleActive(${r.id}, ${r.active ? 0 : 1}, this)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-full transition ${
                    r.active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                }">${r.active ? '✓ Active' : '✗ Inactive'}</button>
            </td>
            <td class="px-5 py-3 text-center">
                <button onclick="deleteDiscount(${r.id}, this)" class="text-red-400 hover:text-red-600 transition p-1 rounded hover:bg-red-50" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </button>
            </td>
        `;
        return tr;
    }

    // --- Inline validation helper (not in a form) ---
    function validateInlineField(field, val) {
        if (field === 'percentage') {
            if (val === '' || isNaN(val)) return 'Enter a valid number.';
            if (Number(val) < 0 || Number(val) > 100) return 'Must be 0–100.';
        }
        if (field === 'min_nights' || field === 'days_before_checkin') {
            if (val !== '' && val !== null) {
                if (isNaN(val)) return 'Enter a valid number.';
                if (!Number.isInteger(Number(val)) || Number(val) < 1) return 'Must be a whole number ≥ 1.';
            }
        }
        return null;
    }

    // --- Inline editing ---
    tbody.addEventListener('click', function(e) {
        const cell = e.target.closest('.editable-cell');
        if (!cell || cell.querySelector('input')) return;

        const oldVal = cell.textContent.trim();
        const field = cell.dataset.field;
        const id = cell.dataset.id;

        const input = document.createElement('input');
        input.type = 'number';
        input.value = oldVal === '—' ? '' : oldVal;
        input.min = '0';
        if (field === 'percentage') input.max = '100';
        input.step = field === 'percentage' ? '0.01' : '1';
        input.className = 'w-20 text-right rounded border border-brand-300 bg-brand-50 px-2 py-1 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/25';

        cell.textContent = '';
        cell.appendChild(input);
        input.focus();
        input.select();

        async function save() {
            const raw = input.value.trim();
            const err = validateInlineField(field, raw);
            if (err) {
                cell.textContent = oldVal;
                flash(err, 'error');
                return;
            }
            const newVal = raw === '' ? null : parseFloat(raw);
            cell.textContent = newVal !== null ? newVal : '—';

            const displayOld = oldVal === '—' ? null : parseFloat(oldVal);
            if (newVal !== displayOld) {
                try {
                    const res = await fetch(`/api/discounts/${id}`, {
                        method: 'PUT', headers: headers(),
                        body: JSON.stringify({ [field]: newVal }),
                    });
                    if (!res.ok) throw new Error();
                    flash('Updated');
                } catch {
                    cell.textContent = oldVal;
                    flash('Failed to update', 'error');
                }
            }
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') input.blur();
            if (ev.key === 'Escape') { cell.textContent = oldVal; }
        });
    });

    // --- Toggle active ---
    window.toggleActive = async function(id, newVal, btn) {
        try {
            const res = await fetch(`/api/discounts/${id}`, {
                method: 'PUT', headers: headers(),
                body: JSON.stringify({ active: !!newVal }),
            });
            if (!res.ok) throw new Error();
            loadDiscounts();
            flash(newVal ? 'Rule activated' : 'Rule deactivated');
        } catch {
            flash('Failed to toggle', 'error');
        }
    };

    // --- jQuery Validation for discount add form ---
    $.validator.addMethod('wholeNumber', function(value, element) {
        return this.optional(element) || (Number(value) === parseInt(value, 10) && Number(value) >= 1);
    }, 'Must be a whole number ≥ 1.');

    var $dscForm = $('#discountAddForm');
    var dscValidator = $dscForm.validate({
        rules: {
            newPct:       { required: true, number: true, min: 0, max: 100 },
            newMinNights: { required: { depends: function() { return $('#newType').val() === 'long_stay'; } }, number: true, wholeNumber: true },
            newDaysBefore:{ required: { depends: function() { return $('#newType').val() === 'last_minute'; } }, number: true, wholeNumber: true }
        },
        messages: {
            newPct:       { required: 'Enter a valid percentage.', number: 'Enter a valid percentage.', min: 'Must be between 0 and 100.', max: 'Must be between 0 and 100.' },
            newMinNights: { required: 'Min nights is required.', number: 'Must be a valid number.' },
            newDaysBefore:{ required: 'Days before check-in is required.', number: 'Must be a valid number.' }
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        highlight: function(element) {
            $(element).addClass('border-red-400').removeClass('border-slate-200');
        },
        unhighlight: function(element) {
            $(element).removeClass('border-red-400').addClass('border-slate-200');
        },
        submitHandler: function(form, e) {
            e.preventDefault();
            saveNewRule();
        }
    });

    // --- Conditional field visibility ---
    window.onTypeChange = function() {
        const type = document.getElementById('newType').value;
        const mnGroup = document.getElementById('minNightsGroup');
        const dbGroup = document.getElementById('daysBeforeGroup');
        if (type === 'long_stay') {
            mnGroup.classList.remove('hidden');
            dbGroup.classList.add('hidden');
            document.getElementById('newDaysBefore').value = '';
        } else {
            mnGroup.classList.add('hidden');
            dbGroup.classList.remove('hidden');
            document.getElementById('newMinNights').value = '';
        }
        dscValidator.resetForm();
        $dscForm.find('.error').removeClass('error border-red-400').addClass('border-slate-200');
    };

    // --- Add form ---
    window.showAddForm = () => { dscValidator.resetForm(); $dscForm.find('.error').removeClass('error border-red-400').addClass('border-slate-200'); onTypeChange(); addFormEl.classList.remove('hidden'); };
    window.hideAddForm = () => { dscValidator.resetForm(); $dscForm.find('.error').removeClass('error border-red-400').addClass('border-slate-200'); addFormEl.classList.add('hidden'); };

    async function saveNewRule() {
        const type = document.getElementById('newType').value;
        const pct  = document.getElementById('newPct').value;
        const minN = document.getElementById('newMinNights').value;
        const days = document.getElementById('newDaysBefore').value;
        const active = document.getElementById('newActive').value;

        try {
            const res = await fetch('/api/discounts', {
                method: 'POST', headers: headers(),
                body: JSON.stringify({
                    type,
                    percentage: +pct,
                    min_nights: minN ? +minN : null,
                    days_before_checkin: days ? +days : null,
                    active: !!parseInt(active),
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) {
                    var errMap = {};
                    if (data.errors.percentage) errMap.newPct = data.errors.percentage[0];
                    if (data.errors.min_nights) errMap.newMinNights = data.errors.min_nights[0];
                    if (data.errors.days_before_checkin) errMap.newDaysBefore = data.errors.days_before_checkin[0];
                    dscValidator.showErrors(errMap);
                } else {
                    flash(data.message || 'Error adding rule', 'error');
                }
                return;
            }
            flash('Rule added');
            hideAddForm();
            loadDiscounts();
        } catch {
            flash('Network error', 'error');
        }
    }

    // --- Delete ---
    window.deleteDiscount = async function(id, btn) {
        if (!confirm('Delete this discount rule?')) return;
        try {
            const res = await fetch(`/api/discounts/${id}`, { method: 'DELETE', headers: headers() });
            if (!res.ok) throw new Error();
            btn.closest('tr').remove();
            flash('Rule deleted');
            if (!tbody.children.length) emptyEl.classList.remove('hidden');
        } catch {
            flash('Failed to delete', 'error');
        }
    };

    // --- Init ---
    loadDiscounts();
})();
</script>
@endpush
