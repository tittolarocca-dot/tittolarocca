<?php

namespace App\Services;

/**
 * Brennt das Booklola-Wasserzeichen (weisser "BookLola.ch"-Schriftzug) links
 * unten in ein bereits skaliertes GD-Canvas ein. Wird NUR beim Erzeugen der
 * öffentlichen Derivate (card/full) aufgerufen – nie auf dem Original, nie auf
 * winzigen Thumbnails/Avataren. Da die Varianten stets frisch aus dem
 * un-wasserzeichneten Original entstehen, kann nie ein doppeltes WM entstehen.
 */
class Watermark
{
    /** Nur diese Varianten erhalten das Wasserzeichen. */
    public const VARIANTS = ['card', 'full'];

    /** Unter dieser Canvas-Breite wird gar kein WM gesetzt (zu klein/unleserlich). */
    private const MIN_CANVAS_WIDTH = 240;

    private const WIDTH_RATIO = 0.20;   // Zielbreite ~20 % der Bildbreite
    private const MIN_WIDTH   = 110;    // px
    private const MAX_WIDTH   = 440;    // px
    private const MARGIN_RATIO = 0.035; // Abstand vom Rand ~3.5 %
    private const LOGO_OPACITY = 0.85;  // 85 %
    private const PILL_OPACITY = 0.28;  // sehr dezenter dunkler Hintergrund

    /** Gecachte Logo-Bytes (einmal von Disk gelesen). */
    private static ?string $logoBytes = null;

    public static function appliesTo(string $variant): bool
    {
        return in_array($variant, self::VARIANTS, true);
    }

    /**
     * Zeichnet das Wasserzeichen links unten in $canvas. Verändert $canvas
     * in-place. Wirft keine Exception nach aussen – der Aufrufer behandelt das.
     */
    public function apply(\GdImage $canvas): void
    {
        $w = imagesx($canvas);
        $h = imagesy($canvas);
        if ($w < self::MIN_CANVAS_WIDTH) {
            return;
        }

        $logo = $this->logo();
        if (! $logo) {
            return;
        }
        $lw = imagesx($logo);
        $lh = imagesy($logo);

        $margin  = (int) round($w * self::MARGIN_RATIO);
        $targetW = (int) round($w * self::WIDTH_RATIO);
        $targetW = max(self::MIN_WIDTH, min($targetW, self::MAX_WIDTH));
        $targetW = min($targetW, $w - 2 * $margin);           // nie breiter als Bild
        $targetH = max(1, (int) round($lh * $targetW / $lw));

        // Logo skalieren (mit Alpha) und Deckkraft anwenden
        $scaled = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagefilledrectangle($scaled, 0, 0, $targetW, $targetH, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
        imagecopyresampled($scaled, $logo, 0, 0, 0, 0, $targetW, $targetH, $lw, $lh);
        $this->applyOpacity($scaled, self::LOGO_OPACITY);

        $x = $margin;
        $y = $h - $targetH - $margin;

        // Dezenter dunkler, abgerundeter Hintergrund (Lesbarkeit auf hellen Fotos)
        $pad = max(4, (int) round($targetH * 0.42));
        $this->drawPill($canvas, $x, $y, $targetW, $targetH, $pad);

        // Logo einbrennen
        imagealphablending($canvas, true);
        imagecopy($canvas, $scaled, $x, $y, 0, 0, $targetW, $targetH);

        imagedestroy($scaled);
        imagedestroy($logo);
    }

    private function logo(): ?\GdImage
    {
        if (self::$logoBytes === null) {
            $path = public_path('images/logo-booklola.png');
            self::$logoBytes = is_file($path) ? (string) @file_get_contents($path) : '';
        }
        if (self::$logoBytes === '') {
            return null;
        }
        $im = @imagecreatefromstring(self::$logoBytes);
        return $im ?: null;
    }

    /** Reduziert die Deckkraft eines Truecolor-Bildes mit Alpha gleichmässig. */
    private function applyOpacity(\GdImage $img, float $opacity): void
    {
        $w = imagesx($img);
        $h = imagesy($img);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($img, $x, $y);
                $a = ($c >> 24) & 0x7F;
                if ($a === 0x7F) {
                    continue; // vollständig transparent
                }
                $na = 127 - (int) round((127 - $a) * $opacity);
                $na = max(0, min(127, $na));
                imagesetpixel($img, $x, $y, ($na << 24) | ($c & 0xFFFFFF));
            }
        }
    }

    /** Zeichnet eine dezente, halbtransparente, abgerundete dunkle Fläche. */
    private function drawPill(\GdImage $canvas, int $x, int $y, int $w, int $h, int $pad): void
    {
        $pw = $w + 2 * $pad;
        $ph = $h + 2 * $pad;
        $px = max(0, $x - $pad);
        $py = max(0, $y - $pad);

        $pill = imagecreatetruecolor($pw, $ph);
        imagealphablending($pill, false);
        imagesavealpha($pill, true);
        imagefilledrectangle($pill, 0, 0, $pw, $ph, imagecolorallocatealpha($pill, 0, 0, 0, 127));

        // Abgerundetes Rechteck (opak schwarz) auf separater Ebene zeichnen …
        $black = imagecolorallocatealpha($pill, 0, 0, 0, 0);
        $r = max(3, (int) min(round($ph * 0.32), round($pw * 0.12)));
        imagefilledrectangle($pill, $r, 0, $pw - $r - 1, $ph - 1, $black);
        imagefilledrectangle($pill, 0, $r, $pw - 1, $ph - $r - 1, $black);
        imagefilledellipse($pill, $r, $r, 2 * $r, 2 * $r, $black);
        imagefilledellipse($pill, $pw - $r - 1, $r, 2 * $r, 2 * $r, $black);
        imagefilledellipse($pill, $r, $ph - $r - 1, 2 * $r, 2 * $r, $black);
        imagefilledellipse($pill, $pw - $r - 1, $ph - $r - 1, 2 * $r, 2 * $r, $black);

        // … und einmal gleichmässig auf Zieldeckkraft reduzieren (kein Doppeldunkel).
        $this->applyOpacity($pill, self::PILL_OPACITY);

        imagealphablending($canvas, true);
        imagecopy($canvas, $pill, $px, $py, 0, 0, $pw, $ph);
        imagedestroy($pill);
    }
}
