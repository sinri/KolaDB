# KolaDB

Simple Information Storage Service.

> composer require sinri/kola-db

## Structure

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

```json
{
  "action":"drop|edit",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME",
  "object":"OBJECT_NAME",
  "data":{
    "KEY":"VALUE"
  }
}
```

```json
{
  "action":"rename",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME",
  "object":"OBJECT_NAME",
  "change":"NEW_NAME"
}
```

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

### Action for show

```json
{
  "action":"list",
  "cluster":"CLUSTER_NAME",
  "collection":"COLLECTION_NAME"
}
```