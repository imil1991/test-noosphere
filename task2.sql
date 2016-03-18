# a. Для заданного списка товаров получить названия всех категорий, в которых представлены товары;
SELECT C.title
FROM items_categories C, items_in_categories IC
WHERE C.id = IC.category_id AND IC.item_id IN (1, 2, 3, 4, 5)
GROUP BY C.id;

# b. Для заданной категории получить список предложений всех товаров из этой категории и ее дочерних категорий;
SELECT DISTINCT
  I.id,
  I.title,
  I.price
FROM items I
  LEFT JOIN items_in_categories IC ON I.id = IC.item_id
WHERE IC.category_id IN (SELECT C1.id
                         FROM items_categories C
                           INNER JOIN items_categories C1
                             ON (C1.numleft >= C.numleft AND C1.numright <= C.numright)
                         WHERE C.id = 2);

# c. Для заданного списка категорий получить количество предложений товаров в каждой категории;
SELECT IC.category_id, COUNT( I.id) FROM items I, items_in_categories IC
WHERE IC.item_id = I.id AND IC.category_id  IN(2,3,4,5,6) GROUP BY IC.category_id;

# d. Для заданного списка категорий получить общее количество уникальных предложений товара;
SELECT IC.category_id, COUNT(DISTINCT I.id) FROM items I, items_in_categories IC
WHERE IC.item_id = I.id AND IC.category_id  BETWEEN 2 AND 50;

# e. Для заданной категории получить ее полный путь в дереве (breadcrumb, «хлебные крошки»).
SELECT C1.title
FROM items_categories C
  INNER JOIN items_categories C1
    ON (C1.numleft <= C.numleft AND C1.numright >= C.numright)
WHERE C.id = 206