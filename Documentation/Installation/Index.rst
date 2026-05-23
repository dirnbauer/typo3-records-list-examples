.. _installation:

============
Installation
============

Install the extension with Composer. If Composer cannot find these
packages on Packagist, add both GitHub repositories as VCS repositories
in your TYPO3 project's root :file:`composer.json`; Composer does not
inherit repository definitions from dependencies.

.. code-block:: bash
    :caption: Composer installation

    composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
    composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git
    composer require webconsulting/records-list-examples:dev-main

Set up the extension and clear caches:

.. code-block:: bash
    :caption: Extension setup

    ./vendor/bin/typo3 extension:setup -e records_list_examples
    ./vendor/bin/typo3 cache:flush

.. _installation-requirements:

Requirements
============

This release supports TYPO3 v14 only:

* TYPO3 CMS 14.3 or later
* PHP 8.2 through 8.5
* EXT:records_list_types 14.x

TYPO3 v13 support has been dropped.
