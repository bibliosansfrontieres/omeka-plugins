<?php

class Api_PackageManagerPackage extends Omeka_Record_Api_AbstractRecordAdapter
{
    // Get the REST representation of a record.
    public function getRepresentation(Omeka_Record_AbstractRecord $record)
    {
        // Return a PHP array, representing the passed record.
        $representation = array(
            'id' => $record->id,
            'slug' => $record->slug,
            'description' => $record->description,
            'language' => $record->language,
            'last_exportable_modification' => $record->last_exportable_modification,
            'ideascube_name' => $record->ideascube_name,
            'related_items' => $this->getPackageContents($record)
        );

        return $representation;
    }

    private function getPackageContents(Omeka_Record_AbstractRecord $record) {
        $contents = array();
        foreach($record->getContents() as $content) {
            $contents[] = array('item_id' => $content->item_id);
        }
        return $contents;
    }
}