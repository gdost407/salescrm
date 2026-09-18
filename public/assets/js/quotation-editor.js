function scaled(value, places) {
    const text = String(value);
    if (!new RegExp(`^\\d+(?:\\.\\d{0,${places}})?$`).test(text)) throw new Error('Invalid decimal');
    const [whole, fraction = ''] = text.split('.');
    return BigInt(whole + fraction.padEnd(places, '0'));
}

function roundDivide(value, divisor) {
    return (value + divisor / 2n) / divisor;
}

export function money(cents) {
    return `${cents / 100n}.${String(cents % 100n).padStart(2, '0')}`;
}

export function calculateLine(type, rate, quantity, gstRate) {
    const amount = roundDivide(scaled(rate, 2) * scaled(quantity, 3), 1000n);
    const gst = scaled(gstRate, 2);
    const subtotal = type === 'inventory' ? roundDivide(amount * 10000n, 10000n + gst) : amount;
    const tax = type === 'inventory' ? amount - subtotal : roundDivide(subtotal * gst, 10000n);
    return { subtotal, tax, total: subtotal + tax };
}

export function initQuotationEditor({ catalog, snapshots, rows }) {
    const body = document.getElementById('quotation-rows');
    const addButton = document.getElementById('add-quotation-item');
    const template = document.getElementById('quotation-row-template');
    const catalogById = new Map(catalog.map(item => [String(item.id), item]));
    const snapshotsById = new Map(snapshots.map(item => [String(item.id), item]));

    const recalculate = () => {
        let subtotal = 0n, tax = 0n, total = 0n;
        [...body.children].forEach((row, index) => {
            const select = row.querySelector('.item-select');
            const quantity = row.querySelector('.item-quantity');
            const snapshotInput = row.querySelector('.snapshot-id');
            select.name = `items[${index}][catalog_item_id]`;
            quantity.name = `items[${index}][quantity]`;
            snapshotInput.name = `items[${index}][quotation_item_id]`;
            const snapshot = snapshotsById.get(snapshotInput.value);
            const item = snapshot && String(snapshot.catalog_item_id) === select.value ? snapshot : catalogById.get(select.value);
            row.querySelector('.item-hsn').textContent = item?.hsn ?? '';
            row.querySelector('.item-rate').textContent = item?.rate ?? '';
            row.querySelector('.item-gst').textContent = item?.gst_rate ?? '';
            row.querySelector('.rate-basis').textContent = item ? (item.type === 'service' ? 'GST exclusive' : 'GST inclusive') : '';
            let amounts = { subtotal: 0n, tax: 0n, total: 0n };
            if (item && quantity.validity.valid && quantity.value !== '') {
                try { amounts = calculateLine(item.type, item.rate, quantity.value, item.gst_rate); } catch { /* Incomplete input has no preview. */ }
            }
            row.querySelector('.item-subtotal').textContent = money(amounts.subtotal);
            row.querySelector('.item-tax').textContent = money(amounts.tax);
            row.querySelector('.item-total').textContent = money(amounts.total);
            subtotal += amounts.subtotal; tax += amounts.tax; total += amounts.total;
            row.querySelector('.remove-item').disabled = body.children.length === 1;
        });
        document.getElementById('quotation-subtotal').textContent = money(subtotal);
        document.getElementById('quotation-tax').textContent = money(tax);
        document.getElementById('quotation-total').textContent = money(total);
        addButton.disabled = body.children.length >= 100;
    };

    const appendRow = (initial = {}) => {
        if (body.children.length >= 100) return;
        const row = template.content.firstElementChild.cloneNode(true);
        const select = row.querySelector('.item-select');
        select.add(new Option('Select item', ''));
        for (const type of ['service', 'inventory']) {
            const group = document.createElement('optgroup');
            group.label = type === 'service' ? 'Services — GST exclusive' : 'Inventory — GST inclusive';
            catalog.filter(item => item.type === type).forEach(item => group.append(new Option(`${item.name} — INR ${item.rate}`, String(item.id))));
            select.append(group);
        }
        const snapshot = snapshotsById.get(String(initial.quotation_item_id ?? ''));
        if (snapshot && String(snapshot.catalog_item_id) === String(initial.catalog_item_id)) {
            row.querySelector('.snapshot-id').value = snapshot.id;
            const existingOption = [...select.options].find(option => option.value === String(snapshot.catalog_item_id));
            if (existingOption) existingOption.textContent = `${snapshot.name} — quoted INR ${snapshot.rate}`;
            else select.add(new Option(`${snapshot.name} — saved quotation item`, String(snapshot.catalog_item_id)));
        }
        select.value = String(initial.catalog_item_id ?? '');
        row.querySelector('.item-quantity').value = initial.quantity ?? '1';
        select.addEventListener('change', () => {
            row.querySelector('.snapshot-id').value = '';
            if (snapshot) {
                const option = [...select.options].find(option => option.value === String(snapshot.catalog_item_id));
                const current = catalogById.get(String(snapshot.catalog_item_id));
                if (option && current) option.textContent = `${current.name} — INR ${current.rate}`;
                else if (option && option.value !== select.value) option.remove();
            }
            recalculate();
        });
        row.querySelector('.item-quantity').addEventListener('input', recalculate);
        row.querySelector('.remove-item').addEventListener('click', () => { row.remove(); recalculate(); });
        body.append(row);
        recalculate();
    };

    (Array.isArray(rows) && rows.length ? rows : [{}]).forEach(appendRow);
    addButton.addEventListener('click', () => appendRow());
}
