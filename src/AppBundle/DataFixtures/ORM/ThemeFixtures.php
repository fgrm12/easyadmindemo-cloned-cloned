<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\FixturesTrait;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Theme;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Defines the sample front themes to load in the database before running the unit
 * and functional tests. Execute this command to load the data.
 *
 *   $ php bin/console doctrine:fixtures:load
 *
 * See https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class ThemeFixtures extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use FixturesTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getRandomThemeTitles() as $i => $title) {
            $theme = new Theme();

            $theme->setTitle($title);
            $theme->setSummary($this->getRandomThemeSummary());
            $theme->setSlug($this->container->get('slugger')->slugify($theme->getTitle()));
            $theme->setContent($this->getThemeContent());
            // "References" are the way to share objects between fixtures defined
            // in different files. This reference has been added in the UserFixtures
            // file and it contains an instance of the User entity.
            $theme->setAuthor($this->getReference('jane-admin'));
            $theme->setPublishedAt(new \DateTime('now - '.$i.'days'));
            $theme->setimageFile

            // for aesthetic reasons, the first front theme always has 2 tags
            foreach ($this->getRandomTags($i > 0 ? mt_rand(0, 3) : 2) as $tag) {
                $theme->addTag($tag);
            }

            foreach (range(1, 5) as $j) {
                $comment = new Comment();

                $comment->setAuthor($this->getReference('john-user'));
                $comment->setPublishedAt(new \DateTime('now + '.($i + $j).'seconds'));
                $comment->setContent($this->getRandomCommentContent());
                $comment->setTheme($theme);

                $manager->persist($comment);
                $theme->addComment($comment);
            }

            $manager->persist($theme);
        }

        $manager->flush();
    }

    /**
     * Instead of defining the exact order in which the fixtures files must be loaded,
     * this method defines which other fixtures this file depends on. Then, Doctrine
     * will figure out the best order to fit all the dependencies.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    private function getRandomTags($numTags = 0)
    {
        $tags = [];

        if (0 === $numTags) {
            return $tags;
        }

        $indexes = (array) array_rand($this->getTagNames(), $numTags);
        foreach ($indexes as $index) {
            $tags[] = $this->getReference('tag-'.$index);
        }

        return $tags;
    }
}
