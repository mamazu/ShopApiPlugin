<?php

declare(strict_types=1);

namespace Tests\Sylius\ShopApiPlugin\Controller\Product;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Sylius\ShopApiPlugin\Controller\JsonApiTestCase;

final class ShowCatalogBySlugApiTest extends JsonApiTestCase
{
    /**
     * @test
     */
    public function it_shows_paginated_products_from_some_taxon_by_slug(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/brands', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/product_list_page_by_slug_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function it_shows_products_for_sub_taxons_by_slug(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/categories/t-shirts/women-t-shirts', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/product_t_shirt_list_page_by_slug_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function it_shows_paginated_products_from_some_taxon_by_slug_in_different_language(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/marken?locale=de_DE', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/german_product_list_page_by_slug_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function it_shows_second_page_of_paginated_products_from_some_taxon_by_slug(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/brands?limit=1&page=2', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/limited_product_list_page_by_slug_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function it_shows_paginated_products_from_some_taxon_by_slug_without_disabled_products(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->get('sylius.repository.product');

        /** @var ObjectManager $productManager */
        $productManager = $this->get('sylius.manager.product');

        /** @var ProductInterface $simpleProduct */
        $simpleProduct = $productRepository->findOneBy(['code' => 'LOGAN_MUG_CODE']);

        $simpleProduct->disable();
        $productManager->persist($simpleProduct);
        $productManager->flush();

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/brands', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/product_list_page_by_slug_without_disabled_product_response', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function it_shows_paginated_products_from_some_taxon_by_slug_without_disabled_product_variants(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        /** @var ProductVariantRepositoryInterface $productVariantRepository */
        $productVariantRepository = $this->get('sylius.repository.product_variant');

        /** @var \Doctrine\Persistence\ObjectManager $productVariantManager */
        $productVariantManager = $this->get('sylius.manager.product_variant');

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $productVariantRepository->findOneBy(['code' => 'SMALL_RED_LOGAN_HAT_CODE']);

        if (method_exists($productVariant, 'disable')) {
            $productVariant->disable();

            $productVariantManager->persist($productVariant);
            $productVariantManager->flush();
        }

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/brands', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        if (method_exists($productVariant, 'disable')) {
            $this->assertResponse($response, 'product/product_list_page_by_slug_without_disabled_product_variants_response', Response::HTTP_OK);
        } else {
            $this->assertResponse($response, 'product/product_list_page_by_slug_response', Response::HTTP_OK);
        }
    }

    /**
     * TODO check is it possible (test annotation make it fail)
     */
    public function it_shows_sorted_product_list(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/x-man?sorting[createdAt]=desc', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/product_list_page_by_slug_response', Response::HTTP_OK);
    }

    /**
     * TODO check is it possible (test annotation make it fail)
     */
    public function it_expose_only_some_of_products_in_the_list(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $this->client->request('GET', '/shop-api/taxon-products/by-slug/x-man?criteria[search][value]=Logans+Hat', [], [], self::CONTENT_TYPE_HEADER);
        $response = $this->client->getResponse();

        $this->assertResponse($response, 'product/product_list_page_by_slug_response', Response::HTTP_OK);
    }
}
