<?php

namespace App\Command;

use App\Repository\Main\AAKResourceRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'app:resource:cache',
    description: 'Updates resource cache item',
)]
class UpdateResourcesCacheCommand extends Command
{
    public const CACHE_LIFETIME = 'cache-lifetime';

    public function __construct(
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly CacheInterface $resourceCache,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            UpdateResourcesCacheCommand::CACHE_LIFETIME,
            null,
            InputOption::VALUE_OPTIONAL,
            'Cache entry lifetime in seconds. Default 1800 s.',
            60 * 30
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheLifetime = (int) $input->getOption(UpdateResourcesCacheCommand::CACHE_LIFETIME);

        $this->resourceCache->delete('resources-');
        $this->resourceCache->get('resources-', function (CacheItemInterface $cacheItem) use ($cacheLifetime) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission();

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $this->resourceCache->delete('resources-businessPartner');
        $this->resourceCache->get('resources-businessPartner', function (CacheItemInterface $cacheItem) use ($cacheLifetime) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission('businessPartner');

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $this->resourceCache->delete('resources-citizen');
        $this->resourceCache->get('resources-citizen', function (CacheItemInterface $cacheItem) use ($cacheLifetime) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission('citizen');

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        return Command::SUCCESS;
    }
}
