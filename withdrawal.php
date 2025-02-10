<div class="card">
    <div class="card-header">
        <h4 class="card-title">Withdraw Funds</h4>
    </div>
    <div class="card-body">
        <form id="withdrawForm">
            <div class="form-group">
                <label>Select Payment Method</label>
                <select class="form-control" name="method" required>
                    <?php while ($method = pg_fetch_assoc($methods)): ?>
                    <option value="<?php echo $method['code']; ?>" 
                            data-min="<?php echo $method['min_withdrawal']; ?>">
                        <?php echo $method['name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Amount (USD)</label>
                <input type="number" class="form-control" name="amount" min="50" required>
                <small class="text-muted">Available balance: $<?php echo number_format($user['balance'], 2); ?></small>
            </div>

            <div class="form-group">
                <label>Your Wallet Address</label>
                <input type="text" class="form-control" name="wallet_address" required>
            </div>

            <button type="submit" class="cmn-btn w-100 mt-4">
                Request Withdrawal
            </button>
        </form>
    </div>
</div>