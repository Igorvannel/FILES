<!-- invest.php - Modal de paiement crypto -->

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crypto Payment</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="crypto-payment">
                    <div class="currency-select mb-4">
                        <label>Select Currency</label>
                        <select class="form-control" id="cryptoCurrency">
                            <option value="BTC">Bitcoin (BTC)</option>
                            <option value="USDT">Tether (USDT)</option>
                        </select>
                    </div>
                    
                    <div class="wallet-address text-center mb-4">
                        <h6>Send payment to:</h6>
                        <div class="qr-code my-3">
                            <img src="" id="qrCode" alt="QR Code" class="img-fluid">
                        </div>
                        <div class="address-text">
                            <code id="walletAddress" class="d-block p-2 bg-light"></code>
                            <button class="btn btn-sm btn-primary mt-2" onclick="copyAddress()">
                                <i class="las la-copy"></i> Copy Address
                            </button>
                        </div>
                    </div>
                    
                    <div class="payment-details">
                        <div class="alert alert-info">
                            <small>
                                Send exactly <span id="cryptoAmount">0.00</span> <span class="currency-label">BTC</span>
                                <br>Payment will be confirmed automatically
                            </small>
                        </div>
                    </div>

                    <div class="transaction-form mt-4">
                        <div class="form-group">
                            <label>Transaction Hash (optional)</label>
                            <input type="text" class="form-control" id="txHash" placeholder="Enter your transaction hash">
                        </div>
                        <button class="cmn-btn w-100" onclick="submitTransaction()">
                            I have made the payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fonctions JavaScript pour gérer les paiements
function copyAddress() {
    const address = document.getElementById('walletAddress').textContent;
    navigator.clipboard.writeText(address);
    alert('Address copied!');
}

function updateQRCode(currency) {
    const address = currency === 'BTC' ? 'VOTRE_ADRESSE_BTC' : 'VOTRE_ADRESSE_USDT';
    document.getElementById('walletAddress').textContent = address;
    // Générer QR code avec l'API de votre choix
    document.getElementById('qrCode').src = `https://api.qrserver.com/v1/create-qr-code/?data=${address}&size=200x200`;
}

function submitTransaction() {
    const txHash = document.getElementById('txHash').value;
    const currency = document.getElementById('cryptoCurrency').value;
    const amount = document.getElementById('cryptoAmount').textContent;

    fetch('process_crypto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tx_hash: txHash,
            currency: currency,
            amount: amount
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Payment submitted successfully!');
            window.location.href = 'dashboard.php';
        } else {
            alert(data.message);
        }
    });
}

// Écouter les changements de devise
document.getElementById('cryptoCurrency').addEventListener('change', function() {
    updateQRCode(this.value);
});

// Initialiser avec BTC
updateQRCode('BTC');
</script>
