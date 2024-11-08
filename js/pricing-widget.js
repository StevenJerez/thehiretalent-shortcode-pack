document.addEventListener('DOMContentLoaded', function() {
    // Use the localized pricingWidgetData provided by PHP
    const pricingData = pricingWidgetData;

    const formatCurrency = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(value);

    document.querySelectorAll('.pricing-widget').forEach(widget => {
        const numberOfEmployees = widget.querySelector('#numberOfEmployees');
        const billingCycleInputs = widget.querySelectorAll('#monthlyOrYearly input[type="radio"]');
        const pricingTotal = widget.querySelector('.pricing-total');
        const pricingComplete = widget.querySelector('.pricing-complete');

        // Function to update the available employee options based on billing cycle
        const updateAvailableOptions = () => {
            const selectedCycle = [...billingCycleInputs].find(input => input.checked).value;

            // Loop over employee options and disable if the selected billing cycle is inactive
            [...numberOfEmployees.options].forEach(option => {
                const employeeRange = option.value;
                const isCycleActive = pricingData[employeeRange] && pricingData[employeeRange][`${selectedCycle}_active`];

                option.disabled = !isCycleActive && pricingData[employeeRange][selectedCycle] !== 'Custom';
            });

            // Disable billing cycle options based on active status for selected employee range
            const selectedEmployeeRange = numberOfEmployees.value;
            billingCycleInputs.forEach(input => {
                const isBillingActive = pricingData[selectedEmployeeRange] && pricingData[selectedEmployeeRange][`${input.value}_active`];
                input.disabled = !isBillingActive && pricingData[selectedEmployeeRange][input.value] !== 'Custom';
            });
        };

        const updatePrice = () => {
            if (!numberOfEmployees || !billingCycleInputs.length || !pricingTotal) return;

            const employeeCount = numberOfEmployees.value;
            const billingCycle = [...billingCycleInputs].find(input => input.checked).value;
            const priceData = pricingData[employeeCount] && pricingData[employeeCount][billingCycle];

            if (priceData === 'Custom' || !priceData) {
                pricingTotal.textContent = "Custom";
                if (pricingComplete) pricingComplete.textContent = '';
                return;
            }

            const monthlyPrice = pricingData[employeeCount]['monthly'];
            let displayedPrice, totalPrice, discount = 0;

            if (billingCycle === 'annually') {
                totalPrice = pricingData[employeeCount][billingCycle];
                displayedPrice = Math.round(totalPrice / 12);
                discount = (monthlyPrice * 12) - totalPrice;
                if (pricingComplete) pricingComplete.innerHTML = `${formatCurrency(totalPrice)}/year, <span>save ${formatCurrency(discount)}</span>`;
            } else if (billingCycle === 'quarterly') {
                totalPrice = pricingData[employeeCount][billingCycle];
                displayedPrice = Math.round(totalPrice / 3);
                discount = (monthlyPrice * 3) - totalPrice;
                if (pricingComplete) pricingComplete.innerHTML = `${formatCurrency(totalPrice)}/quarter, <span>save ${formatCurrency(discount)}</span>`;
            } else {
                displayedPrice = totalPrice = monthlyPrice;
                if (pricingComplete) pricingComplete.textContent = '';
            }

            pricingTotal.textContent = formatCurrency(displayedPrice);
            pricingTotal.setAttribute('price', totalPrice);
        };

        // Add event listeners for billing cycle changes and employee dropdown changes
        billingCycleInputs.forEach(input => {
            input.addEventListener('change', () => {
                updateAvailableOptions();
                updatePrice();
            });
        });

        numberOfEmployees.addEventListener('change', updatePrice);

        // Initial setup
        updateAvailableOptions();
        updatePrice();
    });
});