<?php
namespace Dompdf\Frame;

use Iterator;
use Dompdf\Frame;
/**
 * Pre-order Iterator
 *
 * Returns frames in preorder traversal order (parent then children)
 *
 * @access private
 * @package dompdf
 */
class FrameTreeIterator implements Iterator {
    /**
     * @var Frame
     */
    protected $_root;
    /**
     * @var array
     */
    protected $_stack = [];
    /**
     * @var int
     */
    protected $_num;
    /**
     * @var Frame|null
     */
    protected $_current;

    /**
     * @param Frame $root
     */
    public function __construct(Frame $root) {
        $this->_root = $root;
        $this->rewind();
    }

    public function rewind(): void {
        $this->_stack = [$this->_root];
        $this->_num = 0;
        $this->_current = $this->_root;
    }

    public function valid(): bool {
        return count($this->_stack) > 0;
    }

    public function key(): mixed {
        return $this->_num;
    }

    public function current(): mixed {
        return $this->_current;
    }

    public function next(): void {
        $b = end($this->_stack);
        // Pop last element
        unset($this->_stack[key($this->_stack)]);
        $this->_num++;
        // Push all children onto the stack in reverse order
        if ($c = $b->get_last_child()) {
            $this->_stack[] = $c;
            while ($c = $c->get_prev_sibling()) {
                $this->_stack[] = $c;
            }
        }
        // Store next current
        $this->_current = end($this->_stack) ?: null;
    }
}
