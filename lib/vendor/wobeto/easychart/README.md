EasyChart
=========
PHP chart library based in Libchart

[![Build Status](https://travis-ci.org/fernandowobeto/easychart.svg?branch=master)](https://travis-ci.org/fernandowobeto/easychart)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fernandowobeto/easychart/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fernandowobeto/easychart/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/fernandowobeto/easychart/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/fernandowobeto/easychart/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wobeto/easychart/v/stable.svg)](https://packagist.org/packages/wobeto/easychart) [![Total Downloads](https://poser.pugx.org/wobeto/easychart/downloads.svg)](https://packagist.org/packages/wobeto/easychart) [![Latest Unstable Version](https://poser.pugx.org/wobeto/easychart/v/unstable.svg)](https://packagist.org/packages/wobeto/easychart) [![License](https://poser.pugx.org/wobeto/easychart/license.svg)](https://packagist.org/packages/wobeto/easychart)

## Features

* Bar charts (horizontal or vertical).
* Line charts.
* Pie charts.
* Single or multiple data sets.

## Installation

Add wobeto/easychart to your composer.json file:

```
"require": {
  "wobeto/easychart": "0.6.0"
}
```

Use composer to install this package.

```
$ composer update
```

## Examples

Vertical Bar Chart
------------------

```php
use Wobeto\EasyChart\VerticalBarChart;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\Point;

$chart = new VerticalBarChart(500,250);

$dataSet = new XYDataSet();
$dataSet->addPoint(new Point("2014", 285));
$dataSet->addPoint(new Point("2015", 34));
$chart->setDataSet($dataSet);

$chart->setTitle("Annual usage for easychart");
echo $chart->render();
```

![Vertical Bar](https://github.com/fernandowobeto/easychart/raw/master/docs/images/vertical_bar.png)

Horizontal Bar Chart
--------------------

```php
use Wobeto\EasyChart\HorizontalBarChart;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\Point;
use Wobeto\EasyChart\View\Primitive\Padding;

$chart = new HorizontalBarChart(500, 250);

$dataSet = new XYDataSet();
$dataSet->addPoint(new Point("/wiki/Instant_messenger", 20));
$dataSet->addPoint(new Point("/wiki/Web_Browser", 35));
$dataSet->addPoint(new Point("/wiki/World_Wide_Web", 68));
$chart->setDataSet($dataSet);

$chart->getPlot()->setGraphPadding(new Padding(5, 30, 20, 140));
$chart->setTitle("Most visited pages");
echo $chart->render();
```

![Horizontal Bar](https://github.com/fernandowobeto/easychart/raw/master/docs/images/horizontal_chart.png)

Pie Chart
---------

```php
use Wobeto\EasyChart\PieChart;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\Point;

$chart = new PieChart(500, 300);

$dataSet = new XYDataSet();
$dataSet->addPoint(new Point("Mozilla Firefox", 46));
$dataSet->addPoint(new Point("Chrome", 40));
$dataSet->addPoint(new Point("Internet Explorer", 12));
$dataSet->addPoint(new Point("Others", 2));
$chart->setDataSet($dataSet);
$chart->setLabelFormat('%01.2f%%');

$chart->setTitle("User agents for www.example.com");
echo $chart->render();
```

![Pie Chart](https://github.com/fernandowobeto/easychart/raw/master/docs/images/pie_chart.png)

Multiple line chart
-------------------

```php
use Wobeto\EasyChart\LineChart;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\XYSeriesDataSet;
use Wobeto\EasyChart\Model\Point;

$chart = new LineChart();

$serie1 = new XYDataSet();
$serie1->addPoint(new Point("06-01", 273));
$serie1->addPoint(new Point("06-02", 421));
$serie1->addPoint(new Point("06-03", 642));
$serie1->addPoint(new Point("06-04", 799));
$serie1->addPoint(new Point("06-05", 1009));
$serie1->addPoint(new Point("06-06", 1106));

$serie2 = new XYDataSet();
$serie2->addPoint(new Point("06-01", 280));
$serie2->addPoint(new Point("06-02", 300));
$serie2->addPoint(new Point("06-03", 212));
$serie2->addPoint(new Point("06-04", 542));
$serie2->addPoint(new Point("06-05", 600));
$serie2->addPoint(new Point("06-06", 850));

$serie3 = new XYDataSet();
$serie3->addPoint(new Point("06-01", 180));
$serie3->addPoint(new Point("06-02", 400));
$serie3->addPoint(new Point("06-03", 512));
$serie3->addPoint(new Point("06-04", 642));
$serie3->addPoint(new Point("06-05", 700));
$serie3->addPoint(new Point("06-06", 900));

$serie4 = new XYDataSet();
$serie4->addPoint(new Point("06-01", 280));
$serie4->addPoint(new Point("06-02", 500));
$serie4->addPoint(new Point("06-03", 612));
$serie4->addPoint(new Point("06-04", 742));
$serie4->addPoint(new Point("06-05", 800));
$serie4->addPoint(new Point("06-06", 1000));

$serie5 = new XYDataSet();
$serie5->addPoint(new Point("06-01", 380));
$serie5->addPoint(new Point("06-02", 600));
$serie5->addPoint(new Point("06-03", 712));
$serie5->addPoint(new Point("06-04", 842));
$serie5->addPoint(new Point("06-05", 900));
$serie5->addPoint(new Point("06-06", 1200));

$dataSet = new XYSeriesDataSet();
$dataSet->addSerie("Product 1", $serie1);
$dataSet->addSerie("Product 2", $serie2);
$dataSet->addSerie("Product 3", $serie3);
$dataSet->addSerie("Product 4", $serie4);
$dataSet->addSerie("Product 5", $serie5);
$chart->setDataSet($dataSet);

$chart->setTitle("Sales for 2014");
$chart->getPlot()->setGraphCaptionRatio(0.62);

echo $chart->render();
```

![Multiple Line Chart](https://github.com/fernandowobeto/easychart/raw/master/docs/images/multiple_line_chart.png)

Multiple vertical bar chart
-------------------

```php
use Wobeto\EasyChart\VerticalBarChart;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\XYSeriesDataSet;
use Wobeto\EasyChart\Model\Point;

$chart = new VerticalBarChart();

$serie1 = new XYDataSet();
$serie1->addPoint(new Point("YT", 64));
$serie1->addPoint(new Point("NT", 63));
$serie1->addPoint(new Point("BC", 58));
$serie1->addPoint(new Point("AB", 58));
$serie1->addPoint(new Point("SK", 46));

$serie2 = new XYDataSet();
$serie2->addPoint(new Point("YT", 61));
$serie2->addPoint(new Point("NT", 60));
$serie2->addPoint(new Point("BC", 56));
$serie2->addPoint(new Point("AB", 57));
$serie2->addPoint(new Point("SK", 52));

$dataSet = new XYSeriesDataSet();
$dataSet->addSerie("2013", $serie1);
$dataSet->addSerie("2014", $serie2);
$chart->setDataSet($dataSet);
$chart->getPlot()->setGraphCaptionRatio(0.65);

$chart->setTitle("Average family income (k$)");
echo $chart->render();
```

![Multiple Vertical Bar](https://github.com/fernandowobeto/easychart/raw/master/docs/images/multiple_vertical_bar_chart.png)

Multiple horizontal bar chart
-------------------

```php
use Wobeto\EasyChart\HorizontalBarChart;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\XYSeriesDataSet;
use Wobeto\EasyChart\Model\Point;

$chart = new HorizontalBarChart(500, 250);

$serie1 = new XYDataSet();
$serie1->addPoint(new Point("18-24", 22));
$serie1->addPoint(new Point("25-34", 17));
$serie1->addPoint(new Point("35-44", 20));
$serie1->addPoint(new Point("45-54", 25));

$serie2 = new XYDataSet();
$serie2->addPoint(new Point("18-24", 13));
$serie2->addPoint(new Point("25-34", 18));
$serie2->addPoint(new Point("35-44", 23));
$serie2->addPoint(new Point("45-54", 22));

$dataSet = new XYSeriesDataSet();
$dataSet->addSerie("Male", $serie1);
$dataSet->addSerie("Female", $serie2);
$chart->setDataSet($dataSet);

$chart->setTitle("Firefox vs Chrome users: Age");
echo $chart->render();
```

![Multiple Horizontal Bar](https://github.com/fernandowobeto/easychart/raw/master/docs/images/multiple_horizontal_bar_chart.png)
