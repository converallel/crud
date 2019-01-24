# Crud plugin for CakePHP
This plugin offers features including CRUD operations, infinite scrolling, 
HTTP request logging, soft-deletion, auto-templating.

## Installation

* Install with composer:
  ```
  composer require converallel/crud
  ```

* Load the plugin:
  ```
  bin/cake plugin load Crud
  ```

* Run migrations, this will create `logs` and `http_status_codes` tables in your database: 

  (It is required that you have a `users` table with `int` type `id` in your database.)
  ```
  bin/cake migrations migrate -p Crud
  bin/cake migrations seed -p Crud
  ```

## Usage

### Crud
* Load the component in `AppCrontroller.initialize`:
  ```
  $this->loadComponent('Crud.Crud', [
      //'infiniteScroll' => []
  ]);
  ```

* `bake` a new controller and connect the routes for your controller. The component will handle the
  crud actions (index, view, add, edit, delete) for you.
* If you wish you have customized behaviors for your crud actions:
  * Override the actions in your controller.
  * Write a query that retrieve the desired data.
  * Pass the query to `$this->Crud->load($query)`.
* All HTTP responses will be parsed based on the `Accept` header in the request, i.e., if `Accept` header 
  is set to `application/json`, the response will be json-serialized.

### Infinite Scroll
* If you have **not** loaded `CrudComponent`, load `infiniteScrollComponent` in `AppCrontroller.initialize`:
  ```
  $this->loadComponent('Crud.InfiniteScroll');
  ```

* Use `$this->InfiniteScroll->scroll($object, $settings)` to scroll through an object.
* Use `min_position` or `max_position` in the request query param to facilitate infinite scroll.
  * When scrolling **down**, set `max_position` to the id of the **last** object you have already obtained.
  * When scrolling **up**, set `min_position` to the id of the **first** object you have already obtained.
* Most options found in Pagination can be applied to infiniteScroll, refer to the API documentation for more details.

### Logging HTTP Requests
All the HTTP requests are automatically logged, you should be able to see 
all the requests in the `logs` database table.

### Soft Delete

* Create `deleted_at` column in the database tables that you wish to implement SoftDelete.
* Use the `SoftDeleteTrait` in your ORM table object. 
  Alternatively, you can `bake` the ORM table: ```bin/cake bake model ModelName -t Crud```
* Add these options to the table associations if you wish to cascade soft-deletion.
  ```
  'cascadeCallbacks' => true,
  'dependent' => true
  ```
  
  E.g. If you wish to soft-delete all the articles belong to a user that is being deleted,
  in  `UsersTable.initialize`, you would have this in your table associations:
  ```
  $this->hasMany('Articles', [
      'foreignKey' => 'user_id',
      'cascadeCallbacks' => true,
      'dependent' => true
  ]);
  ```

### Templates
In Progress ...