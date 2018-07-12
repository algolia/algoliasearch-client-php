# Upgrading from v1 to v2

### ObjectID is required for all objects

Because this feature was a mistake (according to API Core team)

### ForwardToReplicas is `true` by default

Because it makes more sense 🙆‍♂️

### ApiKeys can only be managed by the client, not the index

This was already deprecated and has been removed in v2

### Method signature change

Here goes a table with the whole list

| Before                                                      | After                                                                                                 |
|-------------------------------------------------------------|-------------------------------------------------------------------------------------------------------|
| copyIndex('source', 'dest')                                 | copyIndex('source', 'dest')                                                                           |
| scopedCopyIndex('source', 'dest', ['settings', 'synonyms']) | ]copyIndex('source', 'dest', ['scope' => scopedCopyIndex('source', 'dest', ['settings', 'synonyms'])) |
| batchSynonyms($objects, true, false)                        | saveSynonyms($objects)                                                                                |
| batchSynonyms($objects, true, true)                         | freshSynonyms($objects)                                                                               |
| batchSynonyms($objects, false, false)                       | saveSynonyms($objects, ['forwardToReplicas' => false])                                                |
| batchSynonyms($objects, false, true)                        | freshSynonyms($objects, ['forwardToReplicas' => false])                                               |

### Misc

* All `browse*` method return an iterator
* `Index::browseFrom` was removed, use `browse` and pass the cursor in the `$requestOptions`.
Note: this method could easily be added back for DX purpose, as long as it uses browse internally.
