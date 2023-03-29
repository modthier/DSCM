<?php

namespace App\Http\Resources;

use App\Http\Resources\OrderResource;
use App\Http\Resources\StockDetailsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
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
                'drug_amount' => $this->drug_amount,
                'production_date' => $this->production_date,
                'expiration_date' => $this->expiration_date,
                'drug_unit_price' => $this->drug_unit_price,
                'drug_total_cost' => $this->drug_total_cost,
                //'order_id' => new OrderResource($this->Order),
                'stock_details_id' => new StockDetailsResource($this->StockDetails),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            
        ];
    }
}
