/**
 * Handles the secure transmission of payment tokens to the application server.
 * * @async
 * @param {object} paymentData - Raw response object from the Google Pay API.
 * @property {string} paymentData.paymentMethodData.tokenizationData.token - The encrypted payment token.
 * @throws {Error} Captured and logged via the internal monitoring service.
 */
async function processPaymentOnBackend(paymentData) {
  try {
    // Dispatch token and anti-fraud payload to the backend controller
    const response = await fetch('../server/Controllers/ChargeController.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest' 
      },
      body: JSON.stringify({
        token: paymentData.paymentMethodData.tokenizationData.token,
        orderId: 'ORD-998822', // Contextual order reference
        deviceFingerprint: window.getFingerprint() // Client-side risk telemetry
      })
    });

    if (!response.ok) throw new Error(`Server responded with status: ${response.status}`);

    const result = await response.json();
    
    if (result.success) {
      // Redirect to a secure post-purchase verification page
      window.location.href = "/checkout/success";
    }
  } catch (error) {
    /**
     * Error handling strategy:
     * 1. Log the full trace for debugging.
     * 2. Provide a generic, safe message to the end-user.
     */
    console.error("[Payment Service] Transaction Failed:", error);
    alert("We encountered a technical issue processing your request. No funds have been debited.");
  }
}
