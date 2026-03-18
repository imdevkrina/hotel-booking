@extends('layouts.app')

@section('title', 'Manage Inventory — Hotel Booking')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Inventory Management</h1>
            <p class="text-slate-500 text-sm mt-1">Click any price cell to edit. Changes save automatically.</p>
        </div>
        <button onclick="showAddRow()" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
            Add Row
        </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6" id="roomTabs"></div>

    <!-- Status banner -->
    <div id="statusBanner" class="hidden mb-4 rounded-xl px-4 py-3 text-sm font-medium"></div>

    <!-- Add Row Form (hidden by default) -->
    <div id="addRowForm" class="hidden mb-6 bg-white rounded-2xl shadow-md border border-slate-100 p-6">
        <h3 class="text-sm font-bold text-slate-700 mb-4">Add New Inventory Row</h3>
        <form id="inventoryAddForm" novalidate>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Date</label>
                <input type="date" id="newDate" name="newDate" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">1 Person (₹)</label>
                <input type="number" id="newP1" name="newP1" value="2000" min="0" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">2 Persons (₹)</label>
                <input type="number" id="newP2" name="newP2" value="2500" min="0" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">3 Persons (₹)</label>
                <input type="number" id="newP3" name="newP3" value="3000" min="0" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition">Save</button>
                <button type="button" onclick="hideAddRow()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-4 py-2.5 rounded-lg text-sm transition">Cancel</button>
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
                        <th class="text-left px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">Date</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">1 Person (₹)</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">2 Persons (₹)</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide">3 Persons (₹)</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-slate-600 uppercase text-xs tracking-wide w-20">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
        <div id="emptyState" class="hidden py-16 text-center text-slate-400">
            <p class="text-lg font-medium">No inventory rows found.</p>
            <p class="text-sm mt-1">Click "Add Row" to create one.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const csrf  = document.querySelector('meta[name="csrf-token"]').content;
    const tbody = document.getElementById('inventoryBody');
    const tabsEl = document.getElementById('roomTabs');
    const banner = document.getElementById('statusBanner');
    const emptyEl = document.getElementById('emptyState');
    const addForm = document.getElementById('addRowForm');

    let roomTypes = [];
    let activeRoomTypeId = null;

    function headers(extra = {}) {
        return { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, ...extra };
    }

    function flash(msg, type = 'success') {
        banner.className = 'mb-4 rounded-xl px-4 py-3 text-sm font-medium ' +
            (type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700');
        banner.textContent = msg;
        banner.classList.remove('hidden');
        setTimeout(() => banner.classList.add('hidden'), 3000);
    }

    // --- Tabs ---
    async function loadRoomTypes() {
        const res = await fetch('/api/inventory/room-types', { headers: headers() });
        roomTypes = await res.json();
        tabsEl.innerHTML = '';
        roomTypes.forEach((rt, i) => {
            const btn = document.createElement('button');
            btn.textContent = rt.name;
            btn.className = 'px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors';
            btn.onclick = () => selectTab(rt.id);
            btn.dataset.id = rt.id;
            tabsEl.appendChild(btn);
        });
        if (roomTypes.length) selectTab(roomTypes[0].id);
    }

    function selectTab(id) {
        activeRoomTypeId = id;
        tabsEl.querySelectorAll('button').forEach(b => {
            b.className = b.dataset.id == id
                ? 'px-5 py-2.5 rounded-xl text-sm font-semibold bg-brand-600 text-white shadow-sm'
                : 'px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200';
        });
        loadInventory(id);
    }

    // --- Load ---
    async function loadInventory(roomTypeId) {
        const res = await fetch(`/api/inventory?room_type_id=${roomTypeId}`, { headers: headers() });
        const rows = await res.json();
        tbody.innerHTML = '';
        if (!rows.length) {
            emptyEl.classList.remove('hidden');
            return;
        }
        emptyEl.classList.add('hidden');
        rows.forEach(row => tbody.appendChild(buildRow(row)));
    }

    function buildRow(row) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/50 transition-colors';
        tr.dataset.id = row.id;
        tr.innerHTML = `
            <td class="px-5 py-3 font-medium text-slate-700">${row.date}</td>
            <td class="px-5 py-3 text-right">
                <span class="editable-cell cursor-pointer hover:bg-brand-50 rounded px-2 py-1 -mx-2 transition" data-field="price_1_person" data-id="${row.id}">${Number(row.price_1_person)}</span>
            </td>
            <td class="px-5 py-3 text-right">
                <span class="editable-cell cursor-pointer hover:bg-brand-50 rounded px-2 py-1 -mx-2 transition" data-field="price_2_persons" data-id="${row.id}">${Number(row.price_2_persons)}</span>
            </td>
            <td class="px-5 py-3 text-right">
                <span class="editable-cell cursor-pointer hover:bg-brand-50 rounded px-2 py-1 -mx-2 transition" data-field="price_3_persons" data-id="${row.id}">${Number(row.price_3_persons)}</span>
            </td>
            <td class="px-5 py-3 text-center">
                <button onclick="deleteRow(${row.id}, this)" class="text-red-400 hover:text-red-600 transition p-1 rounded hover:bg-red-50" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </button>
            </td>
        `;
        return tr;
    }

    // --- Inline editing ---
    tbody.addEventListener('click', function(e) {
        const cell = e.target.closest('.editable-cell');
        if (!cell || cell.querySelector('input')) return;

        const oldVal = cell.textContent.trim();
        const field  = cell.dataset.field;
        const id     = cell.dataset.id;

        const input = document.createElement('input');
        input.type = 'number';
        input.value = oldVal;
        input.min = '0';
        input.className = 'w-24 text-right rounded border border-brand-300 bg-brand-50 px-2 py-1 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/25';

        cell.textContent = '';
        cell.appendChild(input);
        input.focus();
        input.select();

        async function save() {
            const newVal = input.value.trim();
            const err = validateInlinePrice(newVal);
            if (err) {
                cell.textContent = oldVal;
                flash(err, 'error');
                return;
            }
            cell.textContent = newVal;

            if (newVal !== oldVal) {
                try {
                    const res = await fetch(`/api/inventory/${id}`, {
                        method: 'PUT',
                        headers: headers(),
                        body: JSON.stringify({ [field]: parseFloat(newVal) }),
                    });
                    if (!res.ok) throw new Error();
                    flash('Price updated');
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

    // --- Validation helpers (inline edit only) ---
    function validateInlinePrice(val) {
        if (val === '' || isNaN(val)) return 'Enter a valid number.';
        if (Number(val) < 0) return 'Cannot be negative.';
        return null;
    }

    // --- jQuery Validation for Add Row form ---
    $.validator.addMethod('priceHierarchyP2', function(value, element) {
        var p1 = Number($('#newP1').val());
        var p2 = Number(value);
        if (isNaN(p1) || isNaN(p2)) return true;
        return p2 >= p1;
    }, 'Must be ≥ 1-person price.');

    $.validator.addMethod('priceHierarchyP3', function(value, element) {
        var p2 = Number($('#newP2').val());
        var p3 = Number(value);
        if (isNaN(p2) || isNaN(p3)) return true;
        return p3 >= p2;
    }, 'Must be ≥ 2-persons price.');

    var $invForm = $('#inventoryAddForm');
    var invValidator = $invForm.validate({
        rules: {
            newDate: { required: true },
            newP1:   { required: true, number: true, min: 0 },
            newP2:   { required: true, number: true, min: 0, priceHierarchyP2: true },
            newP3:   { required: true, number: true, min: 0, priceHierarchyP3: true }
        },
        messages: {
            newDate: { required: 'Date is required.' },
            newP1:   { required: 'Enter a valid price.', number: 'Enter a valid price.', min: 'Price cannot be negative.' },
            newP2:   { required: 'Enter a valid price.', number: 'Enter a valid price.', min: 'Price cannot be negative.' },
            newP3:   { required: 'Enter a valid price.', number: 'Enter a valid price.', min: 'Price cannot be negative.' }
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
            saveNewRow();
        }
    });

    // --- Add row ---
    window.showAddRow = () => { invValidator.resetForm(); $invForm.find('.error').removeClass('error border-red-400').addClass('border-slate-200'); addForm.classList.remove('hidden'); };
    window.hideAddRow = () => { invValidator.resetForm(); $invForm.find('.error').removeClass('error border-red-400').addClass('border-slate-200'); addForm.classList.add('hidden'); };

    async function saveNewRow() {
        const date = document.getElementById('newDate').value;
        const p1 = document.getElementById('newP1').value;
        const p2 = document.getElementById('newP2').value;
        const p3 = document.getElementById('newP3').value;

        try {
            const res = await fetch('/api/inventory', {
                method: 'POST',
                headers: headers(),
                body: JSON.stringify({
                    room_type_id: activeRoomTypeId,
                    date, price_1_person: +p1, price_2_persons: +p2, price_3_persons: +p3
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) {
                    if (data.errors.date) invValidator.showErrors({ newDate: data.errors.date[0] });
                    if (data.errors.price_1_person) invValidator.showErrors({ newP1: data.errors.price_1_person[0] });
                    if (data.errors.price_2_persons) invValidator.showErrors({ newP2: data.errors.price_2_persons[0] });
                    if (data.errors.price_3_persons) invValidator.showErrors({ newP3: data.errors.price_3_persons[0] });
                } else {
                    flash(data.message || 'Error adding row', 'error');
                }
                return;
            }
            flash('Row added');
            hideAddRow();
            loadInventory(activeRoomTypeId);
        } catch {
            flash('Network error', 'error');
        }
    }

    // --- Delete ---
    window.deleteRow = async function(id, btn) {
        if (!confirm('Delete this inventory row?')) return;
        try {
            const res = await fetch(`/api/inventory/${id}`, { method: 'DELETE', headers: headers() });
            if (!res.ok) throw new Error();
            btn.closest('tr').remove();
            flash('Row deleted');
            if (!tbody.children.length) emptyEl.classList.remove('hidden');
        } catch {
            flash('Failed to delete', 'error');
        }
    };

    // --- Init ---
    loadRoomTypes();
})();
</script>
@endpush
