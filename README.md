# Attribute Transformer

## Why?

Custom created attributes in backend such as media fields, gives only the id to the template. Which is not useable for Theme Developers.
This plugin transforms given attributes automaticly to media elements or gathering data using model.

#### Before
```
array (size=37)
  'id' => int 5
  'parentId' => int 3
  'name' => string 'Genusswelten' (length=12)
  'changed' => null
  'added' => null
  'attribute' => 
    array (size=10)
      'id' => string '3' (length=1)
      'categoryID' => string '5' (length=1)
      'attribute1' => string '' (length=0)
      'attribute2' => string '' (length=0)
      'attribute3' => string '' (length=0)
      'attribute4' => string '' (length=0)
      'attribute5' => string '' (length=0)
      'attribute6' => string '' (length=0)
      'categoryManufacturers' => string '|1|2|3|4|' (length=9)
```

#### After

````
array (size=37)
  'id' => int 5
  'parentId' => int 3
  'name' => string 'Genusswelten' (length=12)
  'changed' => null
  'added' => null
  'attribute' => 
    array (size=10)
      'id' => string '3' (length=1)
      'categoryID' => string '5' (length=1)
      'attribute1' => string '' (length=0)
      'attribute2' => string '' (length=0)
      'attribute3' => string '' (length=0)
      'attribute4' => string '' (length=0)
      'attribute5' => string '' (length=0)
      'attribute6' => string '' (length=0)
      'categoryManufacturers' => 
        array (size=4)
          1 => 
            array (size=8)
              'name' => string 'shopware AG' (length=11)
              'img' => string '' (length=0)
              'link' => string 'http://www.shopware.de' (length=22)
              'description' => string '' (length=0)
              'meta_title' => null
              'meta_description' => null
              'meta_keywords' => null
              'changed' => string '2018-04-23 11:55:30' (length=19)
          2 => 
            array (size=8)
              'name' => string 'Feinbrennerei Sasse' (length=19)
              'img' => string 'media/image/sasse.png' (length=21)
              'link' => string 'http://www.sassekorn.de' (length=23)
              'description' => string '' (length=0)
              'meta_title' => null
              'meta_description' => null
              'meta_keywords' => null
              'changed' => string '2018-04-23 11:55:30' (length=19)
          3 => 
            array (size=8)
              'name' => string 'Teapavilion' (length=11)
              'img' => string 'media/image/tea.png' (length=19)
              'link' => string 'http://www.teapavilion.com' (length=26)
              'description' => string '' (length=0)
              'meta_title' => null
              'meta_description' => null
              'meta_keywords' => null
              'changed' => string '2018-04-23 11:55:30' (length=19)
          4 => 
            array (size=8)
              'name' => string 'The Deli Garage' (length=15)
              'img' => string 'media/image/deligarage.png' (length=26)
              'link' => string 'http://the-deli-garage.de/' (length=26)
              'description' => string '' (length=0)
              'meta_title' => null
              'meta_description' => null
              'meta_keywords' => null
              'changed' => string '2018-04-23 11:55:30' (length=19)
````

### How to configure it?

* Created a new attribute using backend freetext module (Only works with single / multi selection)
* Add your new attribute to the config.php inside the plugin directory
* Your attribute variable is transformed in template