<?php

//TODO: Move this into .yml and test
if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('WeightBasedShippingRate', 'ExchangeRate_Extension');
}

