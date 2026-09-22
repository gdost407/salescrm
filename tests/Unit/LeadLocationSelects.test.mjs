import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const template = readFileSync(new URL('../../resources/views/app/sales/create-lead.blade.php', import.meta.url), 'utf8');
const script = template.slice(template.lastIndexOf('<script>') + 8, template.lastIndexOf('</script>'))
    .replace(/@json\(old\('country'.*?\)\);/, 'INITIAL_COUNTRY;')
    .replace(/\{\{ route\('locations\.(\w+)'\) \}\}/g, '/$1');

async function form({ country = 'India', state = '', city = '', enhanced = false, request } = {}) {
    class Option {
        constructor(text, value) { this.text = text; this.value = value; }
    }
    class Select {
        value = '';
        disabled = false;
        options = [];
        handlers = {};
        constructor(selected) { this.dataset = { selected }; }
        replaceChildren(...options) { this.options = options; }
        addEventListener(event, callback) { this.handlers[event] = callback; }
        async change(value) { this.value = value; await this.handlers.change(); }
    }
    class TomSelect {
        constructor(select, settings) { this.select = select; this.settings = settings; }
        clear(silent) { assert.equal(silent, true); this.select.value = ''; }
        clearOptions() { this.select.options = this.select.options.filter((option) => option.value === this.select.value); }
        addOptions(options) { this.select.options.push(...options); }
        setValue(value, silent) {
            assert.equal(silent, true);
            this.select.value = this.select.options.some((option) => option.value === value) ? value : '';
        }
        refreshOptions() {}
        enable() { this.select.disabled = false; }
    }
    const selects = { leadCountry: new Select(country), leadState: new Select(state), leadCity: new Select(city) };
    const calls = [];
    let ready;
    vm.runInNewContext(script, {
        INITIAL_COUNTRY: country,
        document: {
            getElementById: (id) => selects[id],
            addEventListener: (event, callback) => { ready = callback; },
        },
        window: enhanced ? { TomSelect } : {},
        Option, URLSearchParams,
        console: { error() {} },
        fetch: async (url) => {
            calls.push(url);
            const data = request ? await request(url) : url.startsWith('/countries')
                ? [{ name: 'India' }, { name: 'Canada' }]
                : url.startsWith('/states') ? [{ name: 'Maharashtra' }] : [{ name: 'Mumbai' }];
            return { ok: true, json: async () => ({ data }) };
        },
    });
    await ready();
    return { country: selects.leadCountry, state: selects.leadState, city: selects.leadCity, calls };
}

for (const enhanced of [false, true]) {
    test(`create form cascades and clears child selections (enhanced=${enhanced})`, async () => {
        const fields = await form({ enhanced });
        assert.equal(fields.country.value, 'India');
        assert.equal(fields.state.options.some((option) => option.value === 'Maharashtra'), true);
        await fields.state.change('Maharashtra');
        assert.equal(fields.city.options.some((option) => option.value === 'Mumbai'), true);
        fields.city.value = 'Mumbai';
        await fields.country.change('');
        assert.equal(fields.state.value, '');
        assert.equal(fields.city.value, '');
        assert.equal(fields.state.options.some((option) => option.value === 'Maharashtra'), false);
    });

    test(`edit matches case and whitespace differences (enhanced=${enhanced})`, async () => {
        const fields = await form({ enhanced, country: ' india ', state: 'maharashtra', city: 'mUMBAI' });
        assert.equal(fields.country.value, 'India');
        assert.equal(fields.state.value, 'Maharashtra');
        assert.equal(fields.city.value, 'Mumbai');
        assert.equal(fields.city.disabled, false);
    });

    test(`unknown webhook state and city remain selectable (enhanced=${enhanced})`, async () => {
        const fields = await form({ enhanced, state: 'MH', city: 'Bombay' });
        assert.equal(fields.state.value, 'MH');
        assert.equal(fields.city.value, 'Bombay');
        assert.equal(fields.state.disabled, false);
        assert.equal(fields.city.disabled, false);
        assert.equal(fields.calls.some((url) => url.startsWith('/cities')), false);
        await fields.state.change('Maharashtra');
        assert.equal(fields.city.value, '');
        assert.equal(fields.city.options.some((option) => option.value === 'Mumbai'), true);
    });
}

test('unrecognized country preserves all saved fields', async () => {
    const fields = await form({ country: 'IN', state: 'MH', city: 'Bombay' });
    assert.equal(fields.country.value, 'IN');
    assert.equal(fields.state.value, 'MH');
    assert.equal(fields.city.value, 'Bombay');
    assert.equal(fields.calls.length, 1);
});

test('network errors preserve saved values and keep fields enabled', async () => {
    const fields = await form({ country: 'India', state: 'MH', city: 'Bombay', request: async () => { throw new Error('offline'); } });
    assert.equal(fields.country.value, 'India');
    assert.equal(fields.state.value, 'MH');
    assert.equal(fields.city.value, 'Bombay');
    assert.equal(fields.country.disabled || fields.state.disabled || fields.city.disabled, false);
});

test('late state response cannot overwrite a newer country selection', async () => {
    let resolveStates;
    const fields = await form({ country: '', request: async (url) => {
        if (url.startsWith('/countries')) return [{ name: 'India' }, { name: 'Canada' }];
        if (url.includes('India')) return new Promise((resolve) => { resolveStates = resolve; });
        return [{ name: 'Ontario' }];
    } });
    const pending = fields.country.change('India');
    await fields.country.change('Canada');
    resolveStates([{ name: 'Maharashtra' }]);
    await pending;
    assert.equal(fields.country.value, 'Canada');
    assert.equal(fields.state.options.some((option) => option.value === 'Ontario'), true);
    assert.equal(fields.state.options.some((option) => option.value === 'Maharashtra'), false);
});
