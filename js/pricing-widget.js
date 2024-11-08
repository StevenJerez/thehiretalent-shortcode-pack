document.addEventListener('DOMContentLoaded', function() {
    const pricingData = pricingWidgetData;
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    };

    document.querySelectorAll('.pricing-widget').forEach(widget => {
        const numberOfEmployees = widget.querySelector('#numberOfEmployees');
        const billingCycleInputs = widget.querySelectorAll('#monthlyOrYearly input[type="radio"]');
        const pricingTotal = widget.querySelector('.pricing-total');
        const pricingComplete = widget.querySelector('.pricing-complete');

        // Function to update the available employee options based on billing cycle
        const updateAvailableOptions = () => {
            const selectedEmployeeRange = numberOfEmployees.value;

            console.log("Selected Employee Range:", selectedEmployeeRange);

            // Check if pricing data exists for the selected employee range
            if (!pricingData[selectedEmployeeRange]) {
                console.warn(`No pricing data found for selected employee range: ${selectedEmployeeRange}`);
                billingCycleInputs.forEach(input => input.disabled = true); // Disable all billing options if range is not found
                return;
            }

            // Update billing cycle radio buttons based on the selected employee range's active status
            billingCycleInputs.forEach(input => {
                const cycle = input.value; // 'monthly', 'quarterly', or 'annually'
                
                // Check if this billing cycle is active for the selected employee range
                const isBillingActive = pricingData[selectedEmployeeRange][`${cycle}_active`] === "on" || pricingData[selectedEmployeeRange][`${cycle}_active`] === true;

                // Disable the billing cycle option if it is explicitly inactive
                input.disabled = !isBillingActive;

                console.log(`Billing Cycle: ${cycle}, Active for ${selectedEmployeeRange}:`, isBillingActive, "| Disabled:", input.disabled);
            });
        };

        // Function to update the displayed price and discount
        const updatePrice = () => {
            if (!numberOfEmployees || !billingCycleInputs.length || !pricingTotal) return;

            const employeeCount = numberOfEmployees.value;
            const billingCycle = [...billingCycleInputs].find(input => input.checked).value;
            const priceData = pricingData[employeeCount] && pricingData[employeeCount][billingCycle];

            if (priceData === 'Custom' || !priceData) {
                pricingTotal.textContent = "Custom";
                pricingTotal.setAttribute('price', "Custom");
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

        // Event listeners to ensure updateAvailableOptions and updatePrice are called on every relevant change
        billingCycleInputs.forEach(input => {
            input.addEventListener('change', () => {
                updateAvailableOptions();
                updatePrice();
            });
        });

        numberOfEmployees.addEventListener('change', () => {
            updateAvailableOptions();
            updatePrice();
        });

        // Initial setup
        updateAvailableOptions();
        updatePrice();
    });
});
