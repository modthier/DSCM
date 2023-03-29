<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'order_description' => $this->order_description,
                'order_date' => $this->order_date,
                'destination_city' => $this->destination_city,
                'destination_address' => $this->destination_address,
                'order_total_cost' => $this->order_total_cost,
                'orderDetails' => OrderDetailsResource::collection($this->OrderDetails),
                'status' => new StatusResource($this->status),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'id' => (string)$this->buyerSeller->id,
                'buyer' => $this->buyerSeller->buyer_id,
                'seller' => $this->buyerSeller->seller_id
            ]
        ];
    }
}
