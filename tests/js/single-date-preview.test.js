import { describe, it, expect } from 'vitest';
import dateRangePickerFormComponent from '../../resources/js/components/date-range-picker.js';

function makeSingleDatePicker(state) {
    const picker = dateRangePickerFormComponent({ state, singleDate: true, dualCalendar: false });
    picker.$watch = () => {};
    picker.$refs = {};
    picker.init();

    return picker;
}

describe('single date hover preview', () => {
    it('marks only the hovered day, without a range back to the current value', () => {
        const picker = makeSingleDatePicker('2026-10-15');

        // Months are zero-based: 9 is October.
        picker.previewDay(20, 9, 2026);

        expect(picker.isDaySelected(20, 9, 2026)).toBe(true);
        expect(picker.isDaySelected(15, 9, 2026)).toBe(false);
        expect(picker.isInRange(17, 9, 2026)).toBe(false);
        expect(picker.hasRange()).toBe(false);
    });
});
