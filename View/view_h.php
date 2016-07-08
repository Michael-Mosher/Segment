<?php
namespace Segment\View;

interface SegmentMaker
{
    
}

interface LinearSegmentMaker extends SegmentMaker
{
    
}

interface HeirarchicalSegmentMaker extends SegmentMaker
{
    
}

interface SolitarySegmentMaker extends SegmentMaker
{
    
}


interface SegmentConstructor
{
    public function segmentConstruct();
}


interface Segment
{
    public function setName($value);
    public function setLocation($value);
    public function setChild(Segement $value);
    public function isFormatValue($value);
    public function setFunction($value);
    public function setType($value);
    public function setValue($value);
    public function setValueOptions($key, $value);
}
