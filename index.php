<?php

use Workerman\Lib\Timer;
use Workerman\Worker;

require 'vendor/autoload.php';


class Fixtures
{

    public $paymentTypes = ['cash', 'tab', 'visa', 'mastercard', 'bitcoin'];

    public $namesArray = ['Ben', 'Jarrod', 'Vijay', 'Aziz'];


    public function getFixturesData(): string
    {
        #create a bunch of random data for various dimensions we want
        $qty = range(1, 4);
        $total = range(30, 1000);
        $tip = range(10, 100);
        $payment_value = range(0, 4);
        $payType = $this->paymentTypes[array_shift($payment_value)];
        $name_value = range(0, 4);
        $name = $this->namesArray[array_shift($name_value)];
        $spent = range(1, 150);
        $year = range(2012, 2016);

        #create a new data point
        $point_data = [
          'quantity' => $qty,
          'total' => $total,
          'tip' => $tip,
          'payType' => $payType,
          'Name' => $name,
          'Spent' => $spent,
          'Year' => $year,
          'x' => time(),
        ];

        #write the json object to the socket
        return json_encode($point_data);
        #create new ioloop instance to intermittently publish data
    }

}

{
    // Create a Websocket server
    $ws_worker = new Worker('websocket://0.0.0.0:2346');
    // 4 processes
    $ws_worker->count = 4;
    $data = new Fixtures();
    // Emitted when new connection come
    $ws_worker->onConnect = function ($connection) use ($data) {
        // 2.5 seconds
        $time_interval = 3.5;
        Timer::add($time_interval,
          function () use ($connection, $data) {
            $connection->send($data->getFixturesData());
          }
        );
    };
    // Emitted when connection closed
    $ws_worker->onClose = function ($connection) {
        echo "Connection closed\n";
    };
    // Run worker
    Worker::runAll();
}