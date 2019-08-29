<?php

namespace AwStudio\Fjord\Form;

use Illuminate\Support\Collection;

class FormCollection extends Collection
{
    public function getCollections()
    {
        return $this->groupBy('collection')->keys()->toArray();
    }

    public function getNames()
    {
        return $this->groupBy('form_name')->keys()->toArray();
    }

    public function hasMultipleCollections()
    {
        return $this->groupBy('collection')->keys()->count() > 1;
    }

    public function hasMultipleNames()
    {
        return $this->groupBy('form_name')->keys()->count() > 1;
    }

    public function getAttribute($key)
    {

        if(array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        $form_field = $this->where('field_id', $key)->first();

        if(! $form_field) {
            return;
        }

        return $form_field->getFormattedFormFieldValue($form_field->form_field);
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }
}
