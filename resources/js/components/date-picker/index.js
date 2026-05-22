/**
 * SheafUI Date Picker Component
 * Author: Mohamed Charrafi
 * Project: SheafUI (https://sheafui.dev)
**/

import DateValue from "../calendar/core/DateValue";

/**
 * Alpine date picker component – production-ready formatter layer
 */
const datePicker = ({
    allPresets = [],
    activatedPresets = [],
    mode = 'range',
    variant,
    rangeSeparator = '→',
}) => {

    return {
        __open: false,
        __activePreset: null,
        __calendar: undefined,
        __selectedDates: [],

        init() {
            let calendarEl = this.$root.querySelector('[x-ref=calendar]');
            // wait for alpine to process the child component  
            queueMicrotask(() => {
                this.__calendar = Alpine.$data(calendarEl);

                // attacht masking to the bounded inputs. 
                this.__calendar.bindInputs({
                    inputEl: this.$refs.date_input,
                    inputStartEl: this.$refs.date_input_start,
                    inputEndEl: this.$refs.date_input_end,
                });

                activatedPresets.length <= 0 || this.__calendar?.presets?.(activatedPresets, allPresets);
                // register the selected dates effects, if the mode is multiple and the variant is pillbox...
                if (variant === 'pills' && this.__calendar && this.__calendar.isMultipleMode()) {
                    Alpine.effect(() => {
                        let state = this.__calendar?.getState();

                        if (!state) { this.__selectedDates = []; return; }

                        this.__selectedDates = state.map(date => {
                            return {
                                value: date,
                                label: DateValue.fromISOString(date).toFormattedString(this.locale, {
                                    day: '2-digit', month: 'short', year: '2-digit',
                                }),
                            };
                        });
                    });
                };
            });
        },

        // -------------------------
        // FORMATTERS CORE
        // -------------------------

        get locale() {
            return this.__calendar?.__locale ?? 'en-US';
        },

        getFormatter(opts) {
            return new Intl.DateTimeFormat(this.locale, opts);
        },

        formatDate(iso) {
            if (!iso) return null;

            const date = DateValue.fromISOString(iso);
            if (!date) return null;

            const today = DateValue.today();
            const diff = date.diffDays(today);

            if (diff === 0) return 'Today';
            if (diff === 1) return 'Tomorrow';
            if (diff === -1) return 'Yesterday';

            return date.toFormattedString(this.locale, {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },

        formatRange(startISO, endISO) {
            if (!startISO && !endISO) return 'Select range';

            const start = DateValue.fromISOString(startISO);
            const end = DateValue.fromISOString(endISO);

            if (!start) return 'Select range';

            // Only start selected
            if (!end) {
                return this.formatDate(startISO);
            }

            const sameYear = start.getYear() === end.getYear();
            const sameMonth = sameYear && start.getMonth() === end.getMonth();

            // Same month + year
            if (sameMonth) {
                const month = start.toFormattedString(this.locale, { month: 'short' });
                const year = start.getYear();

                const startDay = String(start.getDay()).padStart(2, '0');
                const endDay = String(end.getDay()).padStart(2, '0');

                return `${month} ${startDay} ${rangeSeparator} ${endDay}, ${year}`;
            }

            // Same year only
            if (sameYear) {
                const startStr = start.toFormattedString(this.locale, {
                    day: '2-digit',
                    month: 'short',
                });

                const endStr = end.toFormattedString(this.locale, {
                    day: '2-digit',
                    month: 'short',
                });

                return `${startStr} ${rangeSeparator} ${endStr}, ${start.getYear()}`;
            }

            // Different years
            return `${start.toFormattedString(this.locale, {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            })} ${rangeSeparator} ${end.toFormattedString(this.locale, {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            })}`;
        },

        get selectedDates() {
            return this.__selectedDates;
        },

        formatMultiple(state) {
            if (!Array.isArray(state) || !state.length) return 'Select dates';

            if (state.length === 1) {
                return this.formatDate(state[0]);
            }

            const first = this.formatDate(state[0]);
            return `${first} +${state.length - 1}`;
        },

        formatState(state) {
            if (!state) return mode === 'range' ? 'Select range' : 'Select date';

            if (mode === 'single') {
                return this.formatDate(state) ?? 'Select date';
            }

            if (mode === 'range') {
                return this.formatRange(state?.start, state?.end);
            }

            if (mode === 'multiple') {
                return this.formatMultiple(state);
            }

            return 'Select date';
        },

        // -------------------------
        // TRIGGER LABEL (reactive)
        // -------------------------

        get triggerLabel() {
            if (!this.__calendar) return 'Loading...';

            const state = this.__calendar.getState();

            if (!state || (typeof state === 'object' && !Object.keys(state).length)) {
                return 'Select date';
            }

            return this.formatState(state);
        },

        // -------------------------
        // PRESETS
        // -------------------------

        selectPreset(key) {
            requestAnimationFrame(() => {
                const selected = this.__calendar?.selectPresetWithKey(key);

                if (selected) {
                    this.__activePreset = key;
                }

                if (mode === 'single') {
                    this.hide();
                }
            });
        },

        isPresetActive(key) {
            return this.__activePreset === key;
        },

        resetActivePreset() {
            this.__activePreset = null;
        },

        hasSelected() {
            return !!this.__calendar?.getState();
        },

        // -------------------------
        // UI STATE
        // -------------------------

        toggle() {
            this.__open = !this.__open;
        },

        removeDate(date) {
            queueMicrotask(() => {
                this.__calendar?.__removeDate(date)
            });
        },
        reset(date) {
            queueMicrotask(() => {
                this.__calendar?.__reset(date)
            });
        },

        show() {
            this.__open = true;
        },

        hide() {
            this.__open = false;
        },
    };
};

Alpine.data('datePickerComponent', datePicker);

export default datePicker;