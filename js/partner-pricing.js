/* Partner pricing ([partner_pricing]). Each section reads its own data-config,
   so several instances can live on one page. Math is done in cents so that
   member prices round the same way as the PHP render. */
(function () {
    'use strict';

    function money(cents) {
        var out = '$' + Math.floor(cents / 100).toLocaleString('en-US');
        var rest = cents % 100;
        return rest ? out + '.' + String(rest).padStart(2, '0') : out;
    }

    function memberCents(listCents, pct) {
        return Math.round(listCents * (100 - pct) / 100);
    }

    function init(root) {
        if (root.tppReady) return;
        root.tppReady = true;

        var config;
        try {
            config = JSON.parse(root.getAttribute('data-config'));
        } catch (e) {
            return;
        }

        var card = root.querySelector('[data-tpp-unlimited]');
        if (!card) return;

        var size = card.querySelector('[data-tpp-size]');
        var billingBtns = card.querySelectorAll('[data-tpp-billing]');
        var addon = card.querySelector('[data-tpp-addon]');
        var cta = card.querySelector('.tpp-cta');
        var q = function (sel) { return card.querySelectorAll(sel); };

        var state = { size: size.value, billing: 'monthly', addon: false };

        function tier() {
            for (var i = 0; i < config.tiers.length; i++) {
                if (config.tiers[i].key === state.size) return config.tiers[i];
            }
            return config.tiers[0];
        }

        function setText(sel, text) {
            Array.prototype.forEach.call(q(sel), function (el) { el.textContent = text; });
        }

        function render() {
            var t = tier();
            var list = state.billing === 'annual' ? t.annual : t.monthly;
            var custom = list === null;

            q('[data-tpp-priced]')[0].hidden = custom;
            q('[data-tpp-custom]')[0].hidden = !custom;

            if (!custom) {
                var listCents = Math.round(list * 100 * (state.addon ? config.addonMultiple : 1));
                var member = memberCents(listCents, config.discountPct);
                var annual = state.billing === 'annual';

                setText('[data-tpp-regular]', money(listCents));
                setText('[data-tpp-member]', money(member));
                setText('[data-tpp-save]', money(listCents - member));
                setText('[data-tpp-period]', annual ? '/year' : '/month');
                q('[data-tpp-annual-note]')[0].hidden = !annual;
                q('[data-tpp-save-extra]')[0].hidden = !annual;
            }

            Array.prototype.forEach.call(billingBtns, function (b) {
                b.setAttribute('aria-pressed', b.getAttribute('data-tpp-billing') === state.billing ? 'true' : 'false');
            });
            addon.setAttribute('aria-checked', state.addon ? 'true' : 'false');

            // Expose the choice to the signup modal / GTM.
            cta.setAttribute('data-size', state.size);
            cta.setAttribute('data-billing', state.billing);
            cta.setAttribute('data-addon', state.addon ? 'integrityfirst-unlimited' : 'none');
        }

        function changed() {
            render();
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'partner_pricing_change',
                pricing_size: state.size,
                pricing_billing: state.billing,
                pricing_addon: state.addon
            });
        }

        size.addEventListener('change', function () {
            state.size = size.value;
            changed();
        });

        Array.prototype.forEach.call(billingBtns, function (b) {
            b.addEventListener('click', function () {
                if (state.billing === b.getAttribute('data-tpp-billing')) return;
                state.billing = b.getAttribute('data-tpp-billing');
                changed();
            });
        });

        addon.addEventListener('click', function () {
            state.addon = !state.addon;
            changed();
        });

        // state.size was read from the <select>, which browsers restore on
        // back/forward, so the first render can differ from the server HTML.
        render();
    }

    function initAll() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-tpp]'), init);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
    else initAll();
})();
