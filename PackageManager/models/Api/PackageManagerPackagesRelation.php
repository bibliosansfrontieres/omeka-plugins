<?php

class Api_PackageManagerPackagesRelation extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        return array(
            'id' => $record->id,
            'package_id' => $record->package_id,
            'item_id' => $record->item_id
        );
    }

}