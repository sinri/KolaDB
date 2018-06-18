# KolaDB

![GitHub](https://img.shields.io/github/release/sinri/KolaDB.svg)
![Packagist](https://img.shields.io/packagist/v/sinri/kola-db.svg)

Simple Information Storage Service.

https://sinri.github.io/KolaDB/index.html

> composer require sinri/kola-db

## Structure

A simple three level object storage structure and simple key-value properties within objects.

```
Cluster::DIR {
    Collection::DIR {
        Object::JSON_FILE {
            field1:value1,
            field2:value2
        }
    }
}
```

## Action

Action grammar is used to communicate with the server to do certain action.

### Action for Drop

```json
{
  "action":"drop",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME",
  "object":"OBJECT_NAME"
}
```

Fields `collection` and `object` are optional. 

### Action for Edit

```json
{
  "action":"edit",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME",
  "object":"OBJECT_NAME",
  "data":{
    "KEY":"VALUE"
  }
}
```

### Action for Rename


```json
{
  "action":"rename",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME",
  "object":"OBJECT_NAME",
  "change":"NEW_NAME"
}
```

Fields `collection` and `object` are optional. 

### Action for Query

```json
{
  "action":"query",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME",
  "query":[
    {
      "method":"EQUAL",
      "field":"FIELD_NAME",
      "reference":"REFERENCE"
    },
    {
      "method":"AND",
      "queries":[
        {
          "method":"EQUAL",
          "field":"FIELD_NAME",
          "reference":"REFERENCE"
        },
        {
          "method":"EQUAL",
          "field":"FIELD_NAME",
          "reference":"REFERENCE"
        }
      ]
    }
  ]
}

```

### Action for List

```json
{
  "action":"list",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME"
}
```

Field `collection` is optional.