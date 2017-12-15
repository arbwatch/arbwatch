<html>
<head>
<title>Arbitrage - LTC</title>
<meta name="viewport" content="width=device-width" intitial-scale="1" maximum-scale="1">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">

</head>    
<body>

<?php 


$apis = [
    'MBC_LTC' => 'https://www.mercadobitcoin.net/api/LTC/ticker/',
    'MBC_BTC' => 'https://www.mercadobitcoin.net/api/BTC/ticker/',
    'POL' => 'https://poloniex.com/public?command=returnTicker',
    'BTC_fee' => 'https://bitcoinfees.earn.com/api/v1/fees/recommended'
];


$mbc_ltc = json_decode(file_get_contents($apis['MBC_LTC']));  

$mbc_btc = json_decode(file_get_contents($apis['MBC_BTC'])); 

$polo = json_decode(file_get_contents($apis['POL']));  


// var_dump($mbc);
$poloniex = [
    'LTCBTC' => [
        'sell' => (float)$polo->BTC_LTC->lowestAsk,
        'buy' => (float)$polo->BTC_LTC->highestBid
    ]    
];


$mercadobitcoin = [
    'BTCBRL' => [
        'sell' => (float)$mbc_btc->ticker->sell,
        'buy' => (float)$mbc_btc->ticker->buy
    ],
    'LTCBRL' => [
        'sell' => (float)$mbc_ltc->ticker->sell,
        'buy' => (float)$mbc_ltc->ticker->buy
    ]   
];

?>


<div class="container">
    <nav class="navbar navbar-default">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">ArbBrazil</a>
        </div>
    </nav>
    
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td>Exchange</td>
                        <td>Pair</td>
                        <td>Sell (lowest ask)</td>
                        <td>Buy (highest bid)</td>
                        <td>Spread %</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Poloniex</td>
                        <td>LTC/BTC</td>
                        <td><?php echo $poloniex['LTCBTC']['sell']?></td>
                        <td><?php echo $poloniex['LTCBTC']['buy']?></td>
                        <td><?php echo number_format((1-($poloniex['LTCBTC']['buy']/$poloniex['LTCBTC']['sell']))*100,4)?>%</td>
                    </tr>
                    <tr>
                        <td>Mercado Bitcoin</td>
                        <td>LTC/BRL</td>
                        <td><?php echo number_format($mercadobitcoin['LTCBRL']['sell'],2)?></td>
                        <td><?php echo number_format($mercadobitcoin['LTCBRL']['buy'],2)?></td>
                        <td><?php echo number_format((1-($mercadobitcoin['LTCBRL']['buy']/$mercadobitcoin['LTCBRL']['sell']))*100,4)?>%</td>
                    </tr>
                    <tr>
                        <td>Mercado Bitcoin</td>
                        <td>BTC/BRL</td>
                        <td><?php echo number_format($mercadobitcoin['BTCBRL']['sell'],2)?></td>
                        <td><?php echo number_format($mercadobitcoin['BTCBRL']['buy'],2)?></td>
                        <td><?php echo number_format((1-($mercadobitcoin['BTCBRL']['buy']/$mercadobitcoin['BTCBRL']['sell']))*100,4)?>%</td>
                    </tr>                    
                    <tr>
                        <td>Mercado Bitcoin</td>
                        <td>LTC/BRL/BTC</td>
                        <td>
                            <?php 
                            $LTCBRLBTC_sell = $mercadobitcoin['LTCBRL']['sell']/$mercadobitcoin['BTCBRL']['buy'];
                            echo number_format($LTCBRLBTC_sell,8); 
                            ?>
                        </td>
                        <td>
                            <?php 
                            $LTCBRLBTC_buy = $mercadobitcoin['LTCBRL']['buy']/$mercadobitcoin['BTCBRL']['sell'];
                            echo number_format($LTCBRLBTC_buy,8); 
                            ?>
                        </td>
                        <td><?php 
                            echo number_format((1-($LTCBRLBTC_buy/$LTCBRLBTC_sell))*100,4)
                        ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    
    
    <?php
    $invest = 0.02; // btc
    
    $order_sell_btcbrl = ($invest*$mercadobitcoin['BTCBRL']['sell'])*(1-0.003);
    
    $order_buy_ltcbrl = $order_sell_btcbrl / $mercadobitcoin['LTCBRL']['buy']*(1-0.003);
    
    $transfered_ltc = $order_buy_ltcbrl * 0.99;
    
    $order_sell_ltcbtc = $transfered_ltc * $poloniex['LTCBTC']['sell']*(1-0.0015);
    
    $btc_fee = json_decode(file_get_contents($apis['BTC_fee']));  
    
    $withdraw_fee_poloniex = 0.0001;
    
    $btc_fee_total = (374 * $btc_fee->fastestFee) / 100000000;
    // https://estimatefee.com/
    
    $transfered_btc = $order_sell_ltcbtc - $withdraw_fee_poloniex - $btc_fee_total;
    $premium = (($transfered_btc-$invest)/$invest)*100;
    
    $class_premium = 'danger';
        
    if ($premium >= 0.5)
        $class_premium = 'success';
    else if ($premium > 0)
        $class_premium = 'warning';
    
    ?>
    
    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <td><strong>BTC > BRL > LTC > BTC</strong></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Investment (BTC)</td>
                    <td>
                        <strong>
                            <?php echo number_format($invest,6) ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td>Sell BTC/BRL @ Mercado Bitcoin</td>
                    <td><?php echo number_format($order_sell_btcbrl,2) ?></td>
                </tr>         
                <tr>
                    <td>Buy LTC/BRL @ Mercado Bitcoin</td>
                    <td><?php echo number_format($order_buy_ltcbrl,6) ?></td>
                </tr>   
                <tr>
                    <td>Deposit LTC @ Poloniex</td>
                    <td><?php echo number_format($transfered_ltc,6) ?></td>
                </tr>
                <tr>
                    <td>Sell LTC/BTC @ Poloniex</td>
                    <td><?php echo number_format($order_sell_ltcbtc,6) ?></td>
                </tr>
                <tr>
                    <td>Deposit BTC @ Mercado Bitcoin</td>
                    <td>
                        <strong>
                            <?php echo number_format($transfered_btc,6) ?>
                        </strong>
                    </td>
                </tr>
                <tr class="alert alert-<?php echo $class_premium ?>">
                    <td>Premium</td>
                    <td>
                        <strong>
                            <?php echo number_format($premium,1) ?>%
                        </strong>
                    </td>
                </tr>
            </table>
        </div>
    
    
        <?php
        $invest = 2;
        $order_sell_ltcbrl = $invest * $mercadobitcoin['LTCBRL']['sell']*(1-0.003);
        $order_buy_btcbrl = $order_sell_ltcbrl / $mercadobitcoin['BTCBRL']['buy']*(1-0.003);
        
        $btc_fee = json_decode(file_get_contents($apis['BTC_fee']));  
        
        $btc_fee_total = (374 * $btc_fee->fastestFee) / 100000000;
        // https://estimatefee.com/
        
        $transfered_btc = $order_sell_ltcbtc - $btc_fee_total;
        $order_buy_ltcbtc = ($transfered_btc / $poloniex['LTCBTC']['buy'])*(1-0.0015);
        
        $transfered_ltc = ($order_buy_ltcbtc) * 0.99;
        $premium = (($transfered_ltc-$invest)/$invest)*100;
        
        $class_premium = 'danger';
        
        if ($premium >= 0.5)
            $class_premium = 'success';
        else if ($premium > 0)
            $class_premium = 'warning';
        
        ?>
   
   
     
        <div class="col-md-6">
            <table class="table table-striped">
                <tr>
                    <td><strong>LTC > BRL > BTC > LTC</strong></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Investment (LTC)</td>
                    <td>
                        <strong>
                            <?php echo number_format($invest,6) ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td>Sell LTC/BRL @ Mercado Bitcoin</td>
                    <td><?php echo number_format($order_sell_ltcbrl, 2) ?></td>
                </tr>   
                <tr>
                    <td>Buy BTC/BRL @ Mercado Bitcoin</td>
                    <td><?php echo number_format($order_buy_btcbrl, 6) ?></td>
                </tr>
                <tr>
                    <td>Deposit BTC @ Poloniex</td>
                    <td><?php echo number_format($transfered_btc, 6) ?></td>
                </tr>
                <tr>
                    <td>Buy LTC/BTC @ Poloniex</td>
                    <td><?php echo number_format($order_buy_ltcbtc, 6) ?></td>
                </tr>
                <tr>
                    <td>Deposit LTC @ Mercado Bitcoin</td>
                    <td>
                        <strong>
                            <?php echo number_format($transfered_ltc, 6) ?>
                        </strong>
                    </td>
                </tr>
              
                <tr class="alert alert-<?php echo $class_premium ?>">
                    <td>Premium</td>
                    <td>
                        <strong>
                            <?php echo number_format($premium,1) ?>%
                        </strong>
                    </td>
                </tr>
            </table>
        </div>
    </div>       
    
</div>


<script>
/* global $ */
</script>

</body>
</html>