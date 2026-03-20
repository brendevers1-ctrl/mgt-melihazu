<?php

namespace Custom\FlatRateFast\Plugin\Helper;

use Magento\Shipping\Helper\Data as OriginHelper;

class Data
{
    public function aroundGetTrackingPopupUrlBySalesModel(OriginHelper $helper,callable $proceed,$model)
    {
        $trackingNumber = $this->getTrackingNumber($model);
        if ($trackingNumber) {
            return "https://t.17track.net/en#nums=".$trackingNumber;
        }

        return $proceed($model);
    }

    private function getTrackingNumber($model)
    {
        $trackingNumber = '';
        if ($model instanceof \Magento\Sales\Model\Order) {
            foreach ($model->getShipmentsCollection() as $shipment) {
                foreach ($shipment->getAllTracks() as $track) {
                    return $track->getTrackNumber();
                }
            }
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment) {
            foreach ($model->getAllTracks() as $track) {
                return $track->getTrackNumber();
            }
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment\Track) {
            return $model->getTrackNumber();
        }

        return $trackingNumber;
    }
}
