<html>
<head>
<title>Arbitrage</title>
<meta name="viewport" content="width=device-width" intitial-scale="1" maximum-scale="1">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">

</head>    
<body>

<?php include 'config.php' ?>


<div class="container">
    <nav class="navbar navbar-default">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">ArbBrazil</a>
        </div>
    </nav>
    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <td>BTC/USD (CEX.IO)</td>
                    <td><?php echo number_format($exchanges['CEX']['last'],2) ?></td>
                </tr>
                <tr>
                    <td>USD/BRL (BACEN)</td>
                    <td>
                        <?php 
                        $usdBrl = $usdBrl->valores->USD->valor;
                        echo number_format($usdBrl,4)
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Investment (BRL)</td>
                    <td><?php echo number_format($brlInvestment,2)?></td>
                </tr>
                <tr>
                    <td>Fees (IOF + Spread = <?php echo ($iof*100).'% + '.($spread*100).'%'?>) (BRL)</td>
                    <td>
                        <?php
                        $creditCardFees = ((($brlInvestment/$usdBrl)*$iof) + (($brlInvestment/$usdBrl)*$spread))*$usdBrl;
                        echo number_format($creditCardFees,2)
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Total Investiment w/ Fees (BRL)</td>
                    <td>
                        <?php
                        $totalInvestment = $brlInvestment + $creditCardFees;
                        echo number_format($totalInvestment,2)
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>CEX.IO Deposit (USD)</td>
                    <td>
                        <?php
                        $cexDepositUsd = $brlInvestment/$usdBrl;
                        echo number_format($cexDepositUsd,2)
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <td>CEX.IO Deposit Fees (<?php 
                    echo 'USD '.$exchanges['CEX']['fee']['deposit']['usd']['fixed'].' + '. 
                        ($exchanges['CEX']['fee']['deposit']['usd']['percentage']*100).'%'
                    ?>) (USD)</td>
                    <td>
                        <?php 
                        $depositFees = $exchanges['CEX']['fee']['deposit']['usd']['fixed'] +
                            ($exchanges['CEX']['fee']['deposit']['usd']['percentage']*$cexDepositUsd);
                        echo number_format($depositFees,2);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>CEX.IO Deposit after Fees (USD)</td>
                    <td>
                        <?php
                        $cexNetDepositUsd = $cexDepositUsd-$depositFees;
                        echo number_format($cexNetDepositUsd,2)
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>BTC bought at CEX.IO</td>
                    <td>
                        <?php
                        $btc = $cexNetDepositUsd/$exchanges['CEX']['last'];
                        echo number_format($btc,6)
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>CEX.IO BTC withdraw fee (BTC)</td>
                    <td>
                        <?php 
                        echo $exchanges['CEX']['fee']['withdraw']['btc']['fixed']
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Estimated BTC mining fee (BTC)</td>
                    <td>
                        <?php 
                        echo $estimatedBtcMiningFee
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>BTC deposit in Brazilian exchanges (BTC)</td>
                    <td>
                        <?php
                        $depositBtc = $btc-$estimatedBtcMiningFee-$exchanges['CEX']['fee']['withdraw']['btc']['fixed'];
                        echo number_format($depositBtc,6)
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Exchange</th>
                            <th>BTC/BRL (USD)</th>
                            <th>Premium</th>
                            <th>Order Fee % (Maker)</th>
                            <th>Sold After fee (BRL)</th>
                            <th>Withdraw Fee %</th>
                            <th>Withdraw Fee (BRL)</th>
                            <th>Withdraw to Bank After Fees (BRL)</th>
                            <th>Profit/Loss (BRL)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($exchanges as $key => $e) { 
                            if ($key == 'CEX') 
                                continue;
                                
                            $btcUsdBrazil = $e['last']/$usdBrl;
                            $premium = ($btcUsdBrazil-$exchanges['CEX']['last'])/$exchanges['CEX']['last']; 
                            $soldBtcBrl = $depositBtc*$e['last'];
                            $orderFee = $e['fee']['order']['maker']*$soldBtcBrl;
                            $soldBtcBrl = $soldBtcBrl - $orderFee;
                            $withdraw = $soldBtcBrl - ($e['fee']['withdraw']['brl']['fixed'] + ($soldBtcBrl*$e['fee']['withdraw']['brl']['percentage']));
                            $profit = $withdraw-$totalInvestment;
                            $profitPercentage = (($withdraw-$totalInvestment)/$totalInvestment)*100;
                        ?>
                        <tr>
                            <td><?php echo $e['name'] ?></td>
                            <td>
                                <?php echo number_format($e['last'],2); ?>
                                <br/>(<?php echo number_format($btcUsdBrazil,2);?>)
                            </td>
                            <td><?php echo number_format($premium*100,2).'%'?></td>
                            <td><?php echo number_format(100*$e['fee']['order']['maker'],2).'%'?></td>
                            <td><?php echo number_format($soldBtcBrl,2) ?></td>
                            <td><?php echo number_format($e['fee']['withdraw']['brl']['percentage']*100,2).'%'?></td>
                            <td><?php echo number_format($e['fee']['withdraw']['brl']['fixed'],2)?></td>
                            <td><?php echo number_format($withdraw,2)?></td>
                            <td>
                                <?php echo number_format($profit,2) ?><br/>
                                (<?php echo number_format($profitPercentage,2) ?>%)
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
/* global $ */
</script>

</body>
</html>