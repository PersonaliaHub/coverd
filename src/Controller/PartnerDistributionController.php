<?php

namespace App\Controller;

use App\Entity\Orders\BulkDistribution;
use App\Entity\Orders\BulkDistributionLineItem;
use App\Entity\Partner;
use App\Transformers\BulkDistributionTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PartnerOrderController
 * @package App\Controller
 *
 * @Route(path="/api/orders/distribution")
 */
class PartnerDistributionController extends OrderController
{
    protected $defaultEntityName = BulkDistribution::class;

    /**
     * @return BulkDistributionLineItem
     */
    protected function createLineItem()
    {
        return new BulkDistributionLineItem();
    }


    /**
     * Save a new partner distribution
     *
     * @Route(path="/", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request) {
        $params = $request->request->all();

        $order = new BulkDistribution();

        if($params['partner']['id']) {
            $newPartner = $this->getEm()->find(Partner::class, $params['partner']['id']);
            $order->setPartner($newPartner);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->checkEditPermissions($order);

        $this->getEm()->persist($order);
        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }

    /**
     * Whole or partial update of a order
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $params = $request->request->all();
        /** @var BulkDistribution $order */
        $order = $this->getOrder($id);

        $this->checkEditPermissions($order);

        if (!$request->get('reason')) {
            $this->checkEditable($order);
        }

        if($params['partner']['id']) {
            $newPartner = $this->getEm()->find(Partner::class, $params['partner']['id']);
            $order->setPartner($newPartner);
        }

        $this->processLineItems($order, $params['lineItems']);
        unset($params['lineItems']);

        $order->applyChangesFromArray($params);

        $this->getEm()->flush();

        return $this->serialize($request, $order);
    }


    protected function getDefaultTransformer()
    {
        return new BulkDistributionTransformer();
    }
}
