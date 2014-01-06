yii-active-collection-decorator
===============================

This extension can decorate any ActiveRecord method which returns list of the ActiveRecord models and you can use collection as a single ActiveRecord model.

##Installation

 - using git: go to the ***application.ext*** directory and clone project<bR>

```bash
$> git clone git@github.com:oncesk/yii-active-collection-decorator.git
```
 - using archive: download archive and unpack it into ***application.ext*** directory

##Yii configuration

You can add extension into ***import*** section into your ***config/main.php*** file for class autoloading

```php

return array(

  ...

  'import' => array(
    'ext.yii-active-collection-decorator.*'
  ),

  ...
);

```

##Usage

Override method which should return list of the ActiveRecord models such as findAll and etc..

Into your ActiveRecord model

```php
/**
 * @property integer $id
 * @property integer $status
 * @property string $name
 * @property string $avatar
 */
class User extends CActiveRecord {

  const STATUS_ACTIVE = 1;
  const STATUS_DELETED = 2;

  ...

  public function findAll($condition = '', $params = array()) {
  	return ActiveCollectionDecorator::createCollection($this, parent::findAll($condition, $params));
  }
  
  ...
  
  public function relations() {
    return array(
			'posts' => array(self::HAS_MANY, 'Post', 'user_id'),
		);
  }
}

```

And now you can

```php

$userCollection = User::model()->findAll();

//  simple foreach
foreach ($userCollection as $user) {
  //  $user is a User model object
}

//  ArrayAccess interface
echo $userCollection[0]->id; //  get id of the first User model in the list of the models

//  fetch any attributes as array
print_r($userCollection->name); //  output: array(0 => 'John Smith', 1 => 'Sara Mitchel', ...)

//  you can set attribute value for all founded models
$userCollection->status = User::STATUS_DELETED;

//  you can save all models
$userCollection->save();

//  deletion
$userCollection->delete();  // all users will be deleted

//  you can get relations
print_r($userCollection->posts);  // in output you can see posts of every user

//  filter models
$newFilteredCollection = $userCollection->filter(function (User $user) {
	return $user->status == User::STATUS_ACTIVE;
}); // will be returned new collection which contains only models with active users

//  get attribute names
print_r($userCollection->attributeNames());

//  get relations
print_r($userCollection->relations());
```
