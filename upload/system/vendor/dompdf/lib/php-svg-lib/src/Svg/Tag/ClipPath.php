<?php

/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Svg\Tag;

class ClipPath extends AbstractTag {
	protected function before($attributes): void {
		$surface = $this->document->getSurface();

		$surface->save();

		$style = $this->makeStyle($attributes);

		$this->setStyle($style);
		$surface->setStyle($style);

		$this->applyTransform($attributes);
	}

	protected function after(): void {
		$this->document->getSurface()->restore();
	}
}
