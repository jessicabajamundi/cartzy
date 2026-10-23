import { test } from 'node:test';
import assert from 'node:assert/strict';
import { previewPrice, scheduleError } from '../../public/js/seller-sale.js';

test('percentage and fixed discounts preview centavo prices', () => {
    assert.deepEqual(previewPrice(10000, 'percentage', '12.50'), { price: 8750, saving: 1250, error: '' });
    assert.deepEqual(previewPrice(10000, 'fixed', '25'), { price: 7500, saving: 2500, error: '' });
    assert.equal(previewPrice(999, 'percentage', '50').price, 499);
    assert.equal(previewPrice(1, 'percentage', '99.99').price, 1);
});

test('invalid discounts do not show a misleading sale price', () => {
    for (const [type, value] of [['fixed', '100'], ['fixed', '101'], ['percentage', '100'], ['percentage', ''], ['fixed', '-1'], ['fixed', '1.001']]) {
        const preview = previewPrice(10000, type, value);
        assert.equal(preview.price, null);
        assert.ok(preview.error);
    }
    assert.equal(previewPrice(10000, 'fixed', '99.99').price, 1);
});

test('no discount restores regular pricing even with a stale invalid amount', () => {
    assert.deepEqual(previewPrice(10000, '', '1000'), { price: 10000, saving: 0, error: '' });
});

test('schedule validation uses PHT and rejects expired or reversed dates', () => {
    const now = Date.parse('2026-10-13T00:00:00Z');
    assert.equal(scheduleError('percentage', '', '', now), '');
    assert.equal(scheduleError('percentage', '2026-10-13T09:00', '2026-10-14T09:00', now), '');
    assert.ok(scheduleError('percentage', '', '2026-10-13T08:00', now));
    assert.ok(scheduleError('percentage', '2026-10-14T09:00', '2026-10-14T09:00', now));
    assert.ok(scheduleError('fixed', '2026-10-15T09:00', '2026-10-14T09:00', now));
    assert.equal(scheduleError('', '', '2026-10-12T08:00', now), '');
});
