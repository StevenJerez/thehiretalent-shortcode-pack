function initBundleWidget(widgetId) {
    // Select elements within the widget scope
    const widget = document.getElementById(widgetId);
    const bundleSize = widget.querySelector(`#bundleQuantity_${widgetId}`);
    const assessmentTypeInputs = widget.querySelectorAll(`#bundleType_${widgetId} input[type="radio"]`);
    const bundleTotal = widget.querySelector('.bundle-total');

    // Bundle data
    const BundleData = {
        "10": { skill: 25, inDepth: 70 },
        "50": { skill: 20, inDepth: 50 },
        "100": { skill: 18, inDepth: 35 },
        "250": { skill: 15, inDepth: 25 },
        "500": { skill: 10, inDepth: 20 },
        "1250": { skill: 5, inDepth: null },
        "1000": { skill: null, inDepth: 15 }
    };

    // Function to update the price
    function updateBundlePrice() {
        const selectedBundleSize = bundleSize.value;
        const assessmentType = [...assessmentTypeInputs].find(input => input.checked).value;
        let price = BundleData[selectedBundleSize][assessmentType];

        // Convert number to currency without cents
        if (!isNaN(price)) {
            price = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price);
        } else {
            price = "N/A";
        }

        bundleTotal.textContent = price;
    }

    // Function to update the available options
    function updateBundleOptions() {
        const assessmentType = [...assessmentTypeInputs].find(input => input.checked).value;

        // Show or hide options based on the selected assessment type
        const option1000 = bundleSize.querySelector('option[value="1000"]');
        const option1250 = bundleSize.querySelector('option[value="1250"]');
        option1000.disabled = (assessmentType === 'skill');
        option1250.disabled = (assessmentType === 'inDepth');
    }

    // Add event listeners
    bundleSize.addEventListener('change', updateBundlePrice);
    assessmentTypeInputs.forEach(input => {
        input.addEventListener('change', updateBundlePrice);
        input.addEventListener('change', updateBundleOptions);
    });

    // Update the price and options initially
    updateBundlePrice();
    updateBundleOptions();
}