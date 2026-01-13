<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .details {
            margin-bottom: 20px;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .footer {
            text-align: center;
            font-size: 0.8em;
            color: #777;
            margin-top: 30px;
        }

        .total {
            font-weight: bold;
            font-size: 1.2em;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Payment Receipt</h2>
            <p>Thank you for your business!</p>
        </div>

        <div class="details">
            <p><strong>Receipt Number:</strong>
                <?php echo htmlspecialchars($receipt_number ?? ''); ?>
            </p>
            <p><strong>Date:</strong>
                <?php echo htmlspecialchars($date ?? date('Y-m-d')); ?>
            </p>
            <p><strong>Customer:</strong>
                <?php echo htmlspecialchars($customer_name ?? 'Valued Customer'); ?>
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </td>
                                <td>
                                    <?php echo number_format($item['amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td>Service Charge</td>
                            <td>
                                <?php echo number_format($amount ?? 0, 2); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p class="total">Total:
                <?php echo number_format($total ?? $amount ?? 0, 2); ?>
            </p>
        </div>

        <div class="footer">
            <p>If you have any questions, please contact support.</p>
        </div>
    </div>
</body>

</html>