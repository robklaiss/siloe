// Demo for the formatGuarani() function

// Import the function (in a real scenario)
// This is not needed if using the function from main.js directly in HTML pages

/**
 * Format a number as Paraguayan Guaraní currency
 * @param {number} amount - The amount to format
 * @returns {string} Formatted currency string
 */
function formatGuarani(amount) {
    return new Intl.NumberFormat('es-PY', {
        style: 'currency',
        currency: 'PYG',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

// Example usage with different values
console.log('Example 1:', formatGuarani(15000));      // ₲ 15.000
console.log('Example 2:', formatGuarani(1234567.89)); // ₲ 1.234.568 (rounded)
console.log('Example 3:', formatGuarani(50));         // ₲ 50

// Additional examples showing different cases
console.log('Large amount:', formatGuarani(9876543210)); // ₲ 9.876.543.210
console.log('Small decimal:', formatGuarani(99.1));      // ₲ 99 (rounded)
console.log('Zero amount:', formatGuarani(0));           // ₲ 0
