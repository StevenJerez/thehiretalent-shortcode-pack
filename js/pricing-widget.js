// Pricing data
const pricingData = {
    "1-25": { annually: 1400, quarterly: 382, monthly: 140 },
    "26-50": { annually: 2990, quarterly: 815, monthly: 299 },
    "51-100": { annually: 3990, quarterly: 1088, monthly: 399 },
    "101-200": { annually: 5490, quarterly: 1497, monthly: 549 },
    "201-300": { annually: 6490, quarterly: 1770, monthly: 649 },
    "301-500": { annually: 7500, quarterly: 2045, monthly: 749 },
    "501-1000": { annually: 11500, quarterly: 3136, monthly: 1149 },
    "1000+": { annually: "Custom", quarterly: "Custom", monthly: "Custom" }
};

// Get elements
const numberOfEmployees = document.getElementById('numberOfEmployees');
const billingCycleInputs = document.querySelectorAll('#monthlyOrYearly input[type="radio"]');
const pricingTotal = document.querySelector('.pricing-total');
const pricingComplete = document.querySelector('.pricing-complete');

// Helper function to format prices in USD currency
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(value);
}

// Function to calculate discount and total price based on billing cycle
function getBillingDetails(employeeCount, billingCycle) {
    const monthlyPrice = pricingData[employeeCount]['monthly'];
    let totalPrice, displayedPrice, discount = 0;

    switch (billingCycle) {
        case 'annually':
            totalPrice = pricingData[employeeCount][billingCycle];
            displayedPrice = Math.round(totalPrice / 12); // Rounded monthly equivalent for annual payment
            discount = (monthlyPrice * 12) - totalPrice;
            break;
        case 'quarterly':
            totalPrice = pricingData[employeeCount][billingCycle];
            displayedPrice = Math.round(totalPrice / 3); // Rounded monthly equivalent for quarterly payment
            discount = (monthlyPrice * 3) - totalPrice;
            break;
        case 'monthly':
            totalPrice = monthlyPrice;
            displayedPrice = monthlyPrice;
            discount = 0;
            break;
    }
    return { displayedPrice, totalPrice, discount };
}

// Function to update the displayed price and discount
function updatePrice() {
    const employeeCount = numberOfEmployees.value;
    const billingCycle = [...billingCycleInputs].find(input => input.checked).value;
    const priceData = pricingData[employeeCount][billingCycle];

    // Check for "Custom" pricing
    if (priceData === 'Custom') {
        pricingTotal.textContent = "Custom";
        pricingComplete.textContent = '';
        pricingTotal.setAttribute('price', 'Custom');
        return;
    }

    // Calculate billing details
    let { displayedPrice, totalPrice, discount } = getBillingDetails(employeeCount, billingCycle);

    // Format and display prices
    pricingTotal.textContent = formatCurrency(displayedPrice); // Monthly equivalent if applicable
    if (billingCycle === 'annually') {
        pricingComplete.innerHTML = `${formatCurrency(totalPrice)}/year, <span>save ${formatCurrency(discount)}</span>`;
    } else if (billingCycle === 'quarterly') {
        pricingComplete.innerHTML = `${formatCurrency(totalPrice)}/quarter, <span>save ${formatCurrency(discount)}</span>`;
    } else {
        pricingComplete.textContent = ''; // No discount message for monthly billing
    }

    pricingTotal.setAttribute('price', formatCurrency(totalPrice));

    // Disable monthly billing for 1-25 and 26-50 employees
    document.querySelector('#monthlyOrYearly input[value="monthly"]').disabled = (employeeCount === '1-25' || employeeCount === '26-50');
}

// Function to update available options based on billing cycle
function updateBillingCycle() {
    const billingCycle = [...billingCycleInputs].find(input => input.checked).value;
    numberOfEmployees.querySelector('option[value="1-25"]').disabled = (billingCycle === 'monthly');
    numberOfEmployees.querySelector('option[value="26-50"]').disabled = (billingCycle === 'monthly');
}

// Add event listeners
numberOfEmployees.addEventListener('change', updatePrice);
billingCycleInputs.forEach(input => {
    input.addEventListener('change', updatePrice);
    input.addEventListener('change', updateBillingCycle);
});

// Initial setup
updatePrice();
updateBillingCycle();