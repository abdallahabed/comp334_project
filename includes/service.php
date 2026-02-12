<?php
class Service {
    public $service_id;
    public $title;
    public $price;
    public $freelancer_id;
    public $delivery_time;
    public $image;
    public $added_at;

    public function __construct($data) {
        $this->service_id = $data['service_id'];
        $this->title = $data['title'];
        $this->price = (float)$data['price'];
        $this->freelancer_id = $data['freelancer_id'];
        $this->delivery_time = (int)$data['delivery_time'];
        $this->image = $data['image'];
        $this->added_at = time();
    }

    public function getFormattedPrice() {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedDelivery() {
        return $this->delivery_time . ' days';
    }

    public function calculateServiceFee() {
        return $this->price * 0.05;
    }

    public function getTotalWithFee() {
        return $this->price + $this->calculateServiceFee();
    }
}
?>
