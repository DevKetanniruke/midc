<?php

namespace App\Services;

use App\Models\Label;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Picqer\Barcode\BarcodeGeneratorSVG;

class LabelBarcodeService
{
    /** Fields printed as a Code128 barcode on the label. */
    public const BARCODE_FIELDS = ['part_number', 'quantity', 'delivery_note', 'supplier_number', 'vendor_batch_lot', 'revision_level'];

    /** @return array<string,string> field => SVG markup */
    public function barcodes(Label $label): array
    {
        $generator = new BarcodeGeneratorSVG();
        $out = [];

        foreach (self::BARCODE_FIELDS as $field) {
            $value = trim((string) $label->{$field});
            if ($value === '') {
                continue;
            }
            $id = Label::FIELDS[$field][2];
            $out[$field] = $generator->getBarcode($id.$value, $generator::TYPE_CODE_128, 2, 60);
        }

        return $out;
    }

    /** Scanned text: part number|vendor batch|quantity|heat lot|revision level (empty values keep their slot). */
    public function qrPayload(Label $label): string
    {
        return implode('|', array_map(
            fn ($field) => trim((string) $label->{$field}),
            ['part_number', 'vendor_batch_lot', 'quantity', 'heat_lot', 'revision_level']
        ));
    }

    /** Link encoded in the QR: opens the public scan page showing the five values (v = the pipe-separated string). */
    public function qrUrl(Label $label): string
    {
        return route('labels.scan', ['v' => $this->qrPayload($label)]);
    }

    public function qr(Label $label, int $size = 160): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 0), new SvgImageBackEnd()));

        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $writer->writeString($this->qrUrl($label)));
    }
}
