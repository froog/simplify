<?php

class SimplifySiteTreeDecorator extends SiteTreeDecorator
{
    public function updateCMSFields(FieldSet &$fields)
    {
        print_r($fields);
    }
}
