<?php
/*
*	WeightBasedShipping_Item extends Item
*/
class WeightBasedShipping_Item extends DataExtension {

	// Calculate the weight of the item
	public function CalculateWeight(){
		$weight = $this->owner->Quantity * $this->owner->Product()->Weight;

		// Overwrite the weight if there is variations
		if($variation = $this->owner->Variation()){
			if($variation->FullWeight() > 0){
				$weight = $this->owner->Quantity * $variation->FullWeight();
			}
		}

		return $weight;
	}
}
